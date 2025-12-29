<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\VentasCab;
use App\Models\VentasDet;
use App\Models\PedidoVentas;
use App\Models\Clientes;
use App\Models\LibroVentas;
use App\Models\CtasCobrar;
use App\Models\Stock;
use App\Models\Deposito;
use App\Models\TipoImpuesto;

class VentasCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
            v.*,

            -- Fechas
            COALESCE(
                TO_CHAR(v.vent_intervalo_fecha_vence, 'YYYY-MM-DD HH24:MI:SS'),
                'N/A'
            ) AS vent_intervalo_fecha_vence,
            v.vent_fecha,
            v.vent_estado,
            COALESCE(v.vent_cant_cuota::varchar, '0') AS vent_cant_cuota,
            v.condicion_pago,

            -- Cliente
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- Empresa / Sucursal
            e.emp_razon_social,
            s.suc_razon_social,

            -- Usuario
            u.name AS encargado,

            -- Pedido de venta
            COALESCE(
                'PEDIDO DE VENTA NRO: ' || TO_CHAR(pv.id, '0000000'),
                'SIN PEDIDO DE VENTA'
            ) AS pedido_venta

        FROM ventas_cab v

        JOIN clientes c 
            ON c.id = v.clientes_id

        JOIN empresa e 
            ON e.id = v.empresa_id

        JOIN sucursal s 
            ON s.empresa_id = v.sucursal_id

        JOIN users u 
            ON u.id = v.user_id

        LEFT JOIN pedidos_ventas pv 
            ON pv.id = v.pedidos_ventas_id
    ");
}
public function store(Request $r)
{
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
        'user_id' => 'required',
        'pedidos_ventas_id' => 'required',
        'clientes_id' => 'required',
        'empresa_id' => 'required',
        'sucursal_id' => 'required'
    ]);

    // Crear cabecera de venta
    $ventacab = VentasCab::create($datosValidados);

    // Buscar pedido de venta (OJO: tu modelo se llama PedidoVentas, no PedidoVentas)
    $pedidoVenta = PedidoVentas::find($r->pedidos_ventas_id);

    if ($pedidoVenta) {

        // Marcar pedido como PROCESADO
        $pedidoVenta->ped_ven_estado = 'PROCESADO';
        $pedidoVenta->save();

        // Obtener detalles del pedido
        $detalles = DB::table('pedidos_ventas_det')
            ->where('pedidos_ventas_id', $pedidoVenta->id)
            ->get();

        foreach ($detalles as $detalle) {

            // Traer item para impuesto y precio
            $item = DB::table('items')
                ->where('id', $detalle->item_id)
                ->first();

            if (!$item) {
                continue; // o podés devolver error si querés
            }

            VentasDet::create([
                'ventas_cab_id'     => $ventacab->id,
                'item_id'           => $detalle->item_id,
                'vent_det_cantidad' => $detalle->det_cantidad,  // <-- AJUSTÁ si tu columna tiene otro nombre
                'vent_det_precio'   => $item->item_precio,      // <-- PRECIO desde ITEMS
                'tipo_impuesto_id'  => $item->tipo_impuesto_id  // <-- IVA desde ITEMS
            ]);
        }
    }

    return response()->json([
        'mensaje' => 'Venta registrada con éxito',
        'tipo'    => 'success',
        'registro'=> $ventacab
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
        'user_id' => 'required',
        'pedidos_ventas_id' => 'required',
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

            $cantidad = $detalle->vent_det_cantidad;

            // Primero devolver a stock
            $stock = Stock::where('item_id', $detalle->item_id)->first();

            if ($stock) {
                $stock->cantidad += $cantidad;
                $stock->save();
            } else {
                // Si no existía stock, creamos
                Stock::create([
                    'item_id'  => $detalle->item_id,
                    'cantidad' => $cantidad
                ]);
            }

            // Si manejás depósito, podés ajustar aquí si corresponde
        }

        $mensaje = 'Venta anulada. Libro de Ventas, Ctas a Cobrar y Stock actualizados.';

    } else {
        $mensaje = 'Venta anulada correctamente. No se generaron movimientos contables ni de stock.';
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
        'user_id' => 'required',
        'clientes_id' => 'required',
        'empresa_id' => 'required',
        'sucursal_id' => 'required'
    ]);

    // Confirmar venta
    $ventacab->update($datosValidados);
    $ventacab->vent_estado = 'CONFIRMADO';
    $ventacab->save();

    $existenDetalles = VentasDet::where('ventas_cab_id', $ventacab->id)->exists();
    if (!$existenDetalles) {
        return response()->json(['error' => 'No existen detalles para esta venta.'], 404);
    }

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

    $cuotas = ($ventacab->condicion_pago === 'CONTADO') ? 1 : ((int)($ventacab->vent_cant_cuota ?? 1));
    if ($cuotas <= 0) $cuotas = 1;

    $totalVenta = array_sum(array_map(fn($x) => $x->total, $agrupado));
    $montoCuota = round($totalVenta / $cuotas, 2);

    $fechaBase = $ventacab->vent_fecha ?? now();

for ($i = 1; $i <= $cuotas; $i++) {

    $fechaVencimiento = ($ventacab->condicion_pago === 'CONTADO')
        ? $fechaBase
        : \Carbon\Carbon::parse($fechaBase)->addMonths($i);

    CtasCobrar::create([
        'ventas_cab_id'             => $ventacab->id,
        'nro_cuota'                 => $i,
        'cta_cob_monto'             => $montoCuota,
        'cta_cob_fecha_vencimiento' => $fechaVencimiento,
        'cta_cob_estado'            => 'PENDIENTE',
        'condicion_pago'            => $ventacab->condicion_pago,
        'created_at'                => now(),
        'updated_at'                => now()
    ]);
}

    $detallesVenta = VentasDet::where('ventas_cab_id', $ventacab->id)->get();

    foreach ($detallesVenta as $detalle) {

        $cantidad = $detalle->vent_det_cantidad;
        $stock = Stock::where('item_id', $detalle->item_id)->first();

        if ($stock) {
            if ($stock->cantidad >= $cantidad) {
                $stock->cantidad -= $cantidad;
                $stock->save();
            } else {
                $restante = $cantidad - $stock->cantidad;
                $stock->cantidad = 0;
                $stock->save();

                $deposito = Deposito::where('item_id', $detalle->item_id)->first();
                if ($deposito) {
                    $deposito->cantidad = max(0, $deposito->cantidad - $restante);
                    $deposito->save();
                }
            }
        } else {
            $deposito = Deposito::where('item_id', $detalle->item_id)->first();
            if ($deposito) {
                $deposito->cantidad = max(0, $deposito->cantidad - $cantidad);
                $deposito->save();
            }
        }
    }

    return response()->json([
        'mensaje' => 'Venta confirmada con éxito. Libro de Ventas, Ctas a Cobrar y Stock actualizados.',
        'tipo'    => 'success',
        'registro'=> $ventacab
    ], 200);
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
            s.empresa_id AS sucursal_id,
            s.suc_razon_social

        FROM ventas_cab v

        JOIN clientes c 
            ON c.id = v.clientes_id

        JOIN empresa e 
            ON e.id = v.empresa_id

        JOIN sucursal s 
            ON s.empresa_id = v.sucursal_id

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
            s.empresa_id AS sucursal_id,
            s.suc_razon_social

        FROM ventas_cab v

        JOIN clientes c 
            ON c.id = v.clientes_id

        JOIN empresa e 
            ON e.id = v.empresa_id

        JOIN sucursal s 
            ON s.empresa_id = v.sucursal_id

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

}
