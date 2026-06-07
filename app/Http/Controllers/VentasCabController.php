<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\VentasCab;
use App\Models\VentasDet;
use App\Models\VentasPedido;
use App\Models\PedidoVentas;
use App\Models\OrdenServCab;
use App\Models\OrdenServVenta;
use App\Models\Clientes;
use App\Models\LibroVentas;
use App\Models\CtasCobrar;
use App\Models\Stock;
use App\Models\Deposito;
use App\Models\TipoImpuesto;

class VentasCabController extends Controller
{
    public function read(Request $r)
{
    $desde = $r->query('desde', now()->startOfMonth()->toDateString());
    $hasta = $r->query('hasta', now()->toDateString());

    return DB::select("
        SELECT
            v.*,
            COALESCE(TO_CHAR(v.vent_intervalo_fecha_vence, 'YYYY-MM-DD HH24:MI:SS'), 'N/A') AS vent_intervalo_fecha_vence,
            v.vent_fecha,
            v.vent_estado,
            COALESCE(v.vent_cant_cuota::varchar, '0') AS vent_cant_cuota,
            v.condicion_pago,
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,
            e.emp_razon_social,
            s.suc_razon_social,
            f.fun_nom || ' ' || f.fun_apellido AS funcionario,
            COALESCE(
                (SELECT STRING_AGG('PED: ' || LPAD(vp2.pedidos_ventas_id::text, 7, '0'), ', ' ORDER BY vp2.id)
                 FROM ventas_pedidos vp2 WHERE vp2.ventas_cab_id = v.id),
                CASE WHEN v.pedidos_ventas_id IS NOT NULL
                     THEN 'PED: ' || LPAD(v.pedidos_ventas_id::text, 7, '0')
                     ELSE 'SIN PEDIDO'
                END
            ) AS pedido_venta,
            t.tim_numero,
            t.tim_fecha_fin AS tim_fecha_fin,
            COALESCE(
                LPAD(COALESCE(t.tim_establecimiento,'001'), 3, '0') || '-' ||
                LPAD(COALESCE(t.tim_punto_expedicion,'001'), 3, '0') || '-' ||
                LPAD(v.vent_nro_comprobante::varchar, 7, '0'),
                ''
            ) AS vent_nro_factura
        FROM ventas_cab v
        JOIN clientes c    ON c.id  = v.clientes_id
        JOIN empresa e     ON e.id  = v.empresa_id
        JOIN sucursal s    ON s.id  = v.sucursal_id
        JOIN funcionario f ON f.id  = v.funcionario_id
        LEFT JOIN timbrado t ON t.id = v.timbrado_id
        WHERE DATE(v.vent_fecha) BETWEEN ?::date AND ?::date
        ORDER BY v.vent_fecha DESC
    ", [$desde, $hasta]);
}
public function store(Request $r)
{
    if ($r->vent_intervalo_fecha_vence === '') {
        $r->merge(['vent_intervalo_fecha_vence' => null]);
    }
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge(['vent_intervalo_fecha_vence' => null, 'vent_cant_cuota' => null]);
    }

    $datosValidados = $r->validate([
        'vent_intervalo_fecha_vence' => 'nullable|date',
        'vent_fecha'                 => 'nullable|date',
        'vent_estado'                => 'required',
        'vent_cant_cuota'            => 'nullable|integer',
        'condicion_pago'             => 'required',
        'funcionario_id'             => 'nullable',
        'clientes_id'                => 'required',
        'empresa_id'                 => 'required',
        'sucursal_id'                => 'required',
    ]);

    $datosValidados['funcionario_id'] = auth()->user()->funcionario_id;

    // Timbrado
    $timbradoId = $r->timbrado_id ?: null;
    if ($timbradoId) {
        $timbrado = \App\Models\Timbrado::find($timbradoId);
        if ($timbrado && $timbrado->tim_estado === 'activo') {
            $datosValidados['timbrado_id']         = $timbrado->id;
            $datosValidados['vent_nro_comprobante'] = $timbrado->siguiente();
        }
    }

    $ventacab = VentasCab::create($datosValidados);

    // ── Pedidos (array JSON) ───────────────────────────────────────────────
    $pedidosIds = json_decode($r->pedidos_ids ?? '[]', true) ?: [];

    foreach ($pedidosIds as $pedidoId) {
        $pedidoId = (int) $pedidoId;
        $pedido   = PedidoVentas::find($pedidoId);
        if (!$pedido || $pedido->ped_ven_estado !== 'CONFIRMADO') continue;

        VentasPedido::create([
            'ventas_cab_id'     => $ventacab->id,
            'pedidos_ventas_id' => $pedidoId,
        ]);

        $pedido->ped_ven_estado = 'PROCESADO';
        $pedido->save();

        $detalles = DB::select("
            SELECT pvd.item_id, pvd.det_cantidad, pvd.deposito_id,
                   i.item_precio, i.tipo_impuesto_id
            FROM pedidos_ventas_det pvd
            JOIN items i ON i.id = pvd.item_id
            WHERE pvd.pedidos_ventas_id = ?
        ", [$pedidoId]);

        foreach ($detalles as $det) {
            $this->upsertVentaDet($ventacab->id, $det->item_id, $det->det_cantidad, $det->item_precio, $det->tipo_impuesto_id, $det->deposito_id ?? null);
        }
    }

    // ── Órdenes de servicio (array JSON de objetos {orden_id, contrato_id}) ─
    $ordenesRaw = json_decode($r->ordenes_ids ?? '[]', true) ?: [];

    foreach ($ordenesRaw as $item) {
        $ordenId    = (int) ($item['orden_id'] ?? $item);
        $contratoId = isset($item['contrato_id']) && $item['contrato_id'] ? (int) $item['contrato_id'] : null;

        $detallesOrden = DB::table('orden_serv_det')
            ->where('orden_serv_cab_id', $ordenId)
            ->get();

        foreach ($detallesOrden as $det) {
            $this->upsertVentaDet($ventacab->id, $det->item_id, $det->orden_serv_det_cantidad, $det->orden_serv_det_costo, $det->tipo_impuesto_id, null);
        }

        OrdenServVenta::create([
            'ventas_cab_id'        => $ventacab->id,
            'orden_serv_cab_id'    => $ordenId,
            'contrato_serv_cab_id' => $contratoId,
        ]);

        // Marcar la orden de servicio como PROCESADA
        OrdenServCab::where('id', $ordenId)->update(['ord_serv_estado' => 'PROCESADO']);
    }

    return response()->json([
        'mensaje'  => 'Venta registrada con éxito',
        'tipo'     => 'success',
        'registro' => $ventacab
    ], 201);
}

public function update(Request $r, $id)
{
    // Buscar la venta
    $ventacab = VentasCab::find($id);

    if (!$ventacab) {
        return response()->json([
            'mensaje' => 'Venta no encontrada',
            'tipo'    => 'error'
        ], 404);
    }

    // Convertir cadena vacía a null
    if ($r->vent_intervalo_fecha_vence === '') {
        $r->merge(['vent_intervalo_fecha_vence' => null]);
    }

    // Si es CONTADO, limpiar cuotas y vencimiento
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge([
            'vent_intervalo_fecha_vence' => null,
            'vent_cant_cuota' => null
        ]);
    }

    // Validación
    $datosValidados = $r->validate([
        'vent_intervalo_fecha_vence' => 'nullable|date',
        'vent_fecha' => 'nullable|date',
        'vent_estado' => 'required',
        'vent_cant_cuota' => 'nullable|integer',
        'condicion_pago' => 'required',
        'pedidos_ventas_id' => 'nullable|integer',
        'clientes_id' => 'required',
        'empresa_id' => 'required',
        'sucursal_id' => 'required'
    ]);

    // Asegurar null en contado
    if ($r->condicion_pago === 'CONTADO') {
        $datosValidados['vent_intervalo_fecha_vence'] = null;
        $datosValidados['vent_cant_cuota'] = null;
    }

    // Actualizar cabecera
    $ventacab->update($datosValidados);

    return response()->json([
        'mensaje' => 'Venta modificada con éxito',
        'tipo'    => 'success',
        'registro'=> $ventacab
    ], 200);
}
public function anular(Request $r, $id)
{
    // Buscar la venta
    $ventacab = VentasCab::find($id);

    if (!$ventacab) {
        return response()->json([
            'mensaje' => 'Venta no encontrada',
            'tipo'    => 'error'
        ], 404);
    }

    // Guardar estado anterior
    $estadoAnterior = $ventacab->vent_estado;

    // Actualizar estado a ANULADO
    $ventacab->vent_estado = 'ANULADO';
    $ventacab->save();

    if ($estadoAnterior === 'CONFIRMADO') {

        // 🔴 Libro de Ventas
        $libro = LibroVentas::where('ventas_cab_id', $ventacab->id)->first();
        if ($libro) {
            $libro->update([
                'updated_at' => now()
            ]);
            $libro->delete(); // o cambiar estado si preferís
        }

        // 🔴 Cuentas a Cobrar
        $cuentas = CtasCobrar::where('ventas_cab_id', $ventacab->id)->get();
        foreach ($cuentas as $cuota) {
            $cuota->update([
                'cta_cob_estado' => 'ANULADO',
                'updated_at'     => now()
            ]);
        }

        // 🔴 Devolver stock / depósito
        $detallesVenta = VentasDet::where('ventas_cab_id', $ventacab->id)->get();

        foreach ($detallesVenta as $detalle) {
            if (!$detalle->deposito_id) continue;

            $stock = DB::table('stock')
                ->where('deposito_id', $detalle->deposito_id)
                ->where('item_id', $detalle->item_id)->first();
            if ($stock) {
                DB::table('stock')
                    ->where('deposito_id', $detalle->deposito_id)
                    ->where('item_id', $detalle->item_id)
                    ->update(['cantidad' => $stock->cantidad + $detalle->vent_det_cantidad, 'updated_at' => now()]);
            }
        }

        $mensaje = 'Venta anulada. Libro de Ventas, Ctas a Cobrar y Stock actualizados.';

    } else {
        $mensaje = 'Venta anulada correctamente. No se generaron movimientos contables ni de stock.';
    }

    // Revertir pedidos del pivot a CONFIRMADO
    $pedidosPivot = DB::table('ventas_pedidos')->where('ventas_cab_id', $ventacab->id)->get();
    foreach ($pedidosPivot as $pv) {
        DB::table('pedidos_ventas')
            ->where('id', $pv->pedidos_ventas_id)
            ->update(['ped_ven_estado' => 'CONFIRMADO', 'updated_at' => now()]);
    }

    // Revertir pedido legacy (col directa) si no está en el pivot
    if ($ventacab->pedidos_ventas_id && $pedidosPivot->isEmpty()) {
        DB::table('pedidos_ventas')
            ->where('id', $ventacab->pedidos_ventas_id)
            ->update(['ped_ven_estado' => 'CONFIRMADO', 'updated_at' => now()]);
    }

    return response()->json([
        'mensaje'  => $mensaje,
        'tipo'     => 'success',
        'registro' => $ventacab
    ], 200);
}
public function confirmar(Request $r, $id)
{
    $ventacab = VentasCab::find($id);

    if (!$ventacab) {
        return response()->json(['error' => 'Venta no encontrada.'], 404);
    }

    // Evitar doble confirmación
    if ($ventacab->vent_estado === 'CONFIRMADO') {
        return response()->json([
            'mensaje' => 'La venta ya fue confirmada.',
            'tipo'    => 'warning'
        ], 400);
    }

    // Ajustes por condición de pago
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge([
            'vent_intervalo_fecha_vence' => null,
            'vent_cant_cuota' => null
        ]);
    } elseif ($r->vent_intervalo_fecha_vence === '') {
        $r->merge(['vent_intervalo_fecha_vence' => null]);
    }

    // Validación
    $datosValidados = $r->validate([
        'vent_intervalo_fecha_vence' => 'nullable|date',
        'vent_fecha' => 'nullable|date',
        'vent_estado' => 'required',
        'vent_cant_cuota' => 'nullable|integer',
        'condicion_pago' => 'required',
        'clientes_id' => 'required',
        'empresa_id' => 'required',
        'sucursal_id' => 'required'
    ]);

    // Para CREDITO, vent_cant_cuota es obligatorio y debe ser >= 1
    $condicionPago = $r->condicion_pago;
    $cantCuota     = (int)($r->vent_cant_cuota ?? 0);

    if ($condicionPago === 'CREDITO' && $cantCuota < 1) {
        return response()->json([
            'mensaje' => 'Para pago a crédito debe indicar la cantidad de cuotas (mínimo 1). Edite la venta antes de confirmar.',
            'tipo'    => 'error',
        ], 422);
    }

    $existenDetalles = VentasDet::where('ventas_cab_id', $ventacab->id)->exists();
    if (!$existenDetalles) {
        return response()->json(['error' => 'No existen detalles para esta venta.'], 404);
    }

    DB::beginTransaction();

    try {
        // Confirmar venta
        $ventacab->update($datosValidados);
        $ventacab->vent_estado = 'CONFIRMADO';
        $ventacab->save();

        // Fresh desde DB para tener valores actualizados
        $ventacab = $ventacab->fresh();

        $agrupado = DB::select("
            SELECT
                vd.tipo_impuesto_id,
                ti.tip_imp_nom,
                SUM(vd.vent_det_cantidad * vd.vent_det_precio) AS total
            FROM ventas_det vd
            JOIN tipo_impuesto ti ON ti.id = vd.tipo_impuesto_id
            WHERE vd.ventas_cab_id = ?
            GROUP BY vd.tipo_impuesto_id, ti.tip_imp_nom
        ", [$ventacab->id]);

        $cliente = DB::table('clientes')->where('id', $ventacab->clientes_id)->first();

        foreach ($agrupado as $imp) {
            LibroVentas::create([
                'ventas_cab_id'   => $ventacab->id,
                'libV_monto'      => $imp->total,
                'libV_fecha'      => now(),
                'libV_cuota'      => $ventacab->vent_cant_cuota ?? 1,
                'condicion_pago'  => $ventacab->condicion_pago,
                'clientes_id'     => $ventacab->clientes_id,
                'cli_nombre'      => $cliente->cli_nombre ?? null,
                'cli_apellido'    => $cliente->cli_apellido ?? null,
                'cli_ruc'         => $cliente->cli_ruc ?? null,
                'tipo_impuesto_id'=> $imp->tipo_impuesto_id,
                'tip_imp_nom'     => $imp->tip_imp_nom,
                'libV_estado'     => 'ACTIVO',
                'created_at'      => now(),
                'updated_at'      => now()
            ]);
        }

        $cuotas = ($ventacab->condicion_pago === 'CONTADO') ? 1 : (int)($ventacab->vent_cant_cuota ?? 1);
        if ($cuotas <= 0) $cuotas = 1;

        $totalVenta  = array_sum(array_map(fn($x) => (float)$x->total, $agrupado));
        $montoCuota  = round($totalVenta / $cuotas, 2);
        // La última cuota absorbe la diferencia de redondeo para que la suma sea exacta
        $montoUltima = round($totalVenta - ($montoCuota * ($cuotas - 1)), 2);
        $fechaBase   = $ventacab->vent_fecha ?? now();

        for ($i = 1; $i <= $cuotas; $i++) {
            $fechaVencimiento = ($ventacab->condicion_pago === 'CONTADO')
                ? $fechaBase
                : \Carbon\Carbon::parse($fechaBase)->addMonths($i);

            CtasCobrar::create([
                'ventas_cab_id'             => $ventacab->id,
                'nro_cuota'                 => $i,
                'cta_cob_monto'             => ($i === $cuotas) ? $montoUltima : $montoCuota,
                'cta_cob_fecha_vencimiento' => $fechaVencimiento,
                'cta_cob_estado'            => 'PENDIENTE',
                'condicion_pago'            => $ventacab->condicion_pago,
                'created_at'                => now(),
                'updated_at'                => now()
            ]);
        }

        $detallesVenta = VentasDet::where('ventas_cab_id', $ventacab->id)->get();

        foreach ($detallesVenta as $detalle) {
            if (!$detalle->deposito_id) continue;

            $stock = DB::table('stock')
                ->where('deposito_id', $detalle->deposito_id)
                ->where('item_id', $detalle->item_id)->first();
            if ($stock) {
                DB::table('stock')
                    ->where('deposito_id', $detalle->deposito_id)
                    ->where('item_id', $detalle->item_id)
                    ->update(['cantidad' => max(0, $stock->cantidad - $detalle->vent_det_cantidad), 'updated_at' => now()]);
            }
        }

        DB::commit();

        return response()->json([
            'mensaje'  => 'Venta confirmada con éxito. Libro de Ventas, Ctas a Cobrar y Stock actualizados.',
            'tipo'     => 'success',
            'registro' => $ventacab
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'mensaje' => 'Error al confirmar la venta: ' . $e->getMessage(),
            'tipo'    => 'error',
        ], 500);
    }
}
public function buscarVentas(Request $r)
{
    $texto = $r->get('q', '');

    return DB::select("
        SELECT
            v.id AS ventas_cab_id,
            TO_CHAR(v.id, '0000000') AS nro_venta,

            -- Fecha venta
            TO_CHAR(v.vent_fecha, 'dd/mm/yyyy') AS vent_fecha,

            -- Estado
            v.vent_estado,
            v.condicion_pago,

            -- Cliente
            c.id AS clientes_id,
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- Empresa
            e.id AS empresa_id,
            e.emp_razon_social,

            -- Sucursal
            s.id AS sucursal_id,
            s.suc_razon_social

        FROM ventas_cab v

        JOIN clientes c 
            ON c.id = v.clientes_id

        JOIN empresa e 
            ON e.id = v.empresa_id

        JOIN sucursal s 
            ON s.id = v.sucursal_id

        WHERE v.vent_estado = 'CONFIRMADO'

        AND (
            TO_CHAR(v.id, '0000000') ILIKE ?
            OR c.cli_nombre ILIKE ?
            OR c.cli_apellido ILIKE ?
            OR c.cli_ruc ILIKE ?
        )

        ORDER BY v.id DESC
        LIMIT 10
    ", [
        "%$texto%",
        "%$texto%",
        "%$texto%",
        "%$texto%"
    ]);
}
private function upsertVentaDet(int $ventaId, int $itemId, $cantidad, $precio, $tipoImpuestoId, $depositoId): void
{
    $existing = VentasDet::where('ventas_cab_id', $ventaId)
        ->where('item_id', $itemId)
        ->first();

    if ($existing) {
        $existing->vent_det_cantidad += (float) $cantidad;
        $existing->save();
    } else {
        VentasDet::create([
            'ventas_cab_id'     => $ventaId,
            'item_id'           => $itemId,
            'vent_det_cantidad' => $cantidad,
            'vent_det_precio'   => $precio,
            'tipo_impuesto_id'  => $tipoImpuestoId,
            'deposito_id'       => $depositoId,
        ]);
    }
}

public function buscarVentasNota(Request $r)
{
    $texto = $r->get('q', '');

    return DB::select("
        SELECT
            v.id AS ventas_cab_id,
            TO_CHAR(v.id, '0000000') AS venta,

            -- Fecha venta
            TO_CHAR(v.vent_fecha, 'dd/mm/yyyy') AS vent_fecha,

            -- Estado
            v.vent_estado,
            v.condicion_pago,

            -- Vencimiento (solo si es crédito)
            CASE 
                WHEN v.condicion_pago = 'CONTADO' THEN 'N/A'
                ELSE TO_CHAR(v.vent_intervalo_fecha_vence, 'dd/mm/yyyy')
            END AS vent_intervalo_fecha_vence,

            -- Cuotas
            CASE 
                WHEN v.condicion_pago = 'CONTADO' THEN 'N/A'
                ELSE COALESCE(v.vent_cant_cuota::varchar, '1')
            END AS vent_cant_cuota,

            -- Cliente
            c.id AS clientes_id,
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- Empresa
            e.id AS empresa_id,
            e.emp_razon_social,

            -- Sucursal
            s.id AS sucursal_id,
            s.suc_razon_social

        FROM ventas_cab v

        JOIN clientes c 
            ON c.id = v.clientes_id

        JOIN empresa e 
            ON e.id = v.empresa_id

        JOIN sucursal s 
            ON s.id = v.sucursal_id

        WHERE v.vent_estado = 'CONFIRMADO'

        AND (
            TO_CHAR(v.id, '0000000') ILIKE ?
            OR c.cli_nombre ILIKE ?
            OR c.cli_apellido ILIKE ?
            OR c.cli_ruc ILIKE ?
        )

        ORDER BY v.id DESC
        LIMIT 10
    ", [
        "%$texto%",
        "%$texto%",
        "%$texto%",
        "%$texto%"
    ]);
}

public function imprimir($id)
{
    $cab = DB::selectOne("
        SELECT
            v.id,
            v.vent_estado,
            v.condicion_pago,
            v.vent_cant_cuota,
            TO_CHAR(v.vent_fecha, 'DD/MM/YYYY HH24:MI') AS vent_fecha,

            -- Empresa / Sucursal
            e.emp_razon_social,
            e.emp_direccion,
            e.emp_telefono,
            e.emp_correo,
            s.suc_razon_social,
            s.suc_direccion,
            s.suc_telefono,

            -- Cliente
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- Funcionario
            f.fun_nom || ' ' || f.fun_apellido AS funcionario,

            -- Timbrado / Factura
            t.tim_numero,
            COALESCE(t.tim_establecimiento, '001') AS tim_establecimiento,
            COALESCE(t.tim_punto_expedicion, '001') AS tim_punto_expedicion,
            TO_CHAR(t.tim_fecha_fin, 'DD/MM/YYYY')  AS tim_fecha_fin,
            v.vent_nro_comprobante,
            COALESCE(
                LPAD(COALESCE(t.tim_establecimiento,'001'), 3, '0') || '-' ||
                LPAD(COALESCE(t.tim_punto_expedicion,'001'), 3, '0') || '-' ||
                LPAD(v.vent_nro_comprobante::varchar, 7, '0'),
                'S/N'
            ) AS nro_factura

        FROM ventas_cab v
        JOIN clientes   c ON c.id = v.clientes_id
        JOIN empresa    e ON e.id = v.empresa_id
        JOIN sucursal   s ON s.id = v.sucursal_id
        JOIN funcionario f ON f.id = v.funcionario_id
        LEFT JOIN timbrado t ON t.id = v.timbrado_id
        WHERE v.id = ?
    ", [$id]);

    if (!$cab) {
        return response()->json(['mensaje' => 'Venta no encontrada', 'tipo' => 'error'], 404);
    }

    $detalles = DB::select("
        SELECT
            i.id   AS item_id,
            i.item_decripcion,
            vd.vent_det_cantidad    AS cantidad,
            vd.vent_det_precio      AS precio,
            vd.vent_det_cantidad * vd.vent_det_precio AS subtotal,
            ti.tip_imp_nom,
            CASE
                WHEN ti.tip_imp_nom ILIKE '%exent%' THEN
                    ROUND((vd.vent_det_cantidad * vd.vent_det_precio)::numeric, 2)
                ELSE 0
            END AS monto_exenta,
            CASE
                WHEN ti.tip_imp_nom ILIKE '%5%' THEN
                    ROUND((vd.vent_det_cantidad * vd.vent_det_precio / 21)::numeric, 2)
                ELSE 0
            END AS iva5,
            CASE
                WHEN ti.tip_imp_nom ILIKE '%10%' THEN
                    ROUND((vd.vent_det_cantidad * vd.vent_det_precio / 11)::numeric, 2)
                ELSE 0
            END AS iva10
        FROM ventas_det vd
        JOIN items       i  ON i.id  = vd.item_id
        JOIN tipo_impuesto ti ON ti.id = vd.tipo_impuesto_id
        WHERE vd.ventas_cab_id = ?
        ORDER BY i.item_decripcion
    ", [$id]);

    $cuotas = DB::select("
        SELECT
            nro_cuota,
            cta_cob_monto,
            TO_CHAR(cta_cob_fecha_vencimiento, 'DD/MM/YYYY') AS fecha_vencimiento,
            cta_cob_estado
        FROM ctas_cobrar
        WHERE ventas_cab_id = ?
        ORDER BY nro_cuota
    ", [$id]);

    return response()->json([
        'cab'     => $cab,
        'detalles' => $detalles,
        'cuotas'   => $cuotas,
    ]);
}

}
