<?php

namespace App\Http\Controllers;

use App\Models\CompraCab;
use App\Models\OrdenCompraCab; 
use App\Models\CompraDet;
use App\Models\CtasPagar;
use App\Models\LibroCompras;
use App\Models\Stock;
use App\Models\Proveedor;
use App\Models\TipoImpuesto;
use App\Models\Deposito;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompraCabController extends Controller
{
    public function read(Request $r) {
        $desde = $r->query('desde', now()->startOfMonth()->toDateString());
        $hasta = $r->query('hasta', now()->toDateString());

        return DB::select("
            SELECT
                c.*,
                COALESCE(to_char(c.comp_intervalo_fecha_vence, 'YYYY-MM-DD HH24:MI:SS'), 'N/A') AS comp_intervalo_fecha_vence,
                c.comp_fecha,
                c.comp_estado,
                COALESCE(c.comp_cant_cuota::varchar, '0') AS comp_cant_cuota,
                c.condicion_pago,
                p.prov_razonsocial,
                p.prov_ruc,
                p.prov_telefono,
                p.prov_correo,
                e.emp_razon_social,
                s.suc_razon_social,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                COALESCE('ORDEN DE COMPRA NRO: ' || to_char(occ.id, '0000000'), 'SIN ORDEN DE COMPRA') ||
                COALESCE(' VENCE EL: ' || to_char(occ.ord_comp_intervalo_fecha_vence, 'YYYY-MM-DD HH24:MI:SS'), '') AS ordencompra
            FROM compra_cab c
            JOIN proveedores p       ON p.id  = c.proveedor_id
            JOIN empresa e           ON e.id  = c.empresa_id
            JOIN sucursal s          ON s.id  = c.sucursal_id
            JOIN funcionario f       ON f.id  = c.funcionario_id
            LEFT JOIN orden_compra_cab occ ON occ.id = c.orden_compra_cab_id
            WHERE c.comp_fecha BETWEEN ? AND ?
            ORDER BY c.comp_fecha DESC
        ", [$desde, $hasta]);
    }    
    public function store(Request $r) {
        // Convertir cadena vacía a null antes de la validación
        if ($r->comp_intervalo_fecha_vence === '') {
            $r->merge(['comp_intervalo_fecha_vence' => null]);
        }
    
        // Establecer comp_cant_cuota como null si la condición de pago es "CONTADO"
        if ($r->condicion_pago === 'CONTADO') {
            $r->merge(['comp_intervalo_fecha_vence' => null, 'comp_cant_cuota' => null]);
        }
    
        // Validación de datos
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence' => 'nullable|date',
            'comp_fecha'                 => 'nullable|date',
            'comp_estado'                => 'required',
            'comp_cant_cuota'            => 'nullable|integer',
            'condicion_pago'             => 'required',
            'comp_timbrado'              => 'nullable|string|max:20',
            'comp_nro_factura'           => ['nullable','string','max:15','regex:/^\d{3}-\d{3}-\d{7}$/'],
            'comp_fecha_emision'         => 'nullable|date|before_or_equal:today',
            'funcionario_id'             => 'nullable',
            'orden_compra_cab_id'        => 'required',
            'proveedor_id'               => 'required',
            'empresa_id'                 => 'required',
            'sucursal_id'                => 'required'
        ]);

        $datosValidados['funcionario_id'] = auth()->user()->funcionario_id;
        // Crear la cabecera de la compra
        $compracab = CompraCab::create($datosValidados);
    
        // Obtener la orden de compra cabecera
        $ordencompracab = OrdenCompraCab::find($r->orden_compra_cab_id);
        if ($ordencompracab) {
            $ordencompracab->ord_comp_estado = "PROCESADO"; // Cambiar el estado de la orden
            $ordencompracab->save();
    
            // Obtener los detalles de la orden de compra
            $detalles = DB::table('orden_compra_det')
                ->where('orden_compra_cab_id', $ordencompracab->id)
                ->get();
    
            // Insertar los detalles en la tabla compra_det
            foreach ($detalles as $detalle) {
                CompraDet::create([
                    'compra_cab_id'    => $compracab->id,
                    'item_id'          => $detalle->item_id,
                    'comp_det_cantidad'=> $detalle->orden_compra_det_cantidad,
                    'comp_det_costo'   => $detalle->orden_compra_det_costo,
                    'tipo_impuesto_id' => $detalle->tipo_impuesto_id,
                    'deposito_id'      => $detalle->deposito_id,
                    'marca_id'         => $detalle->marca_id  ?? null,
                    'modelo_id'        => $detalle->modelo_id ?? null,
                ]);
            }
        }
        return response()->json([
            'mensaje' => 'Registro creado con éxito',
            'tipo' => 'success',
            'registro' => $compracab
        ], 201);
    }

    public function update(Request $r, $id){
        $compracab = CompraCab::find($id);
        // Convertir cadena vacía a null antes de la validación
    if ($r->comp_intervalo_fecha_vence === '') {
        $r->merge(['comp_intervalo_fecha_vence' => null]);
    }

    // Establecer   _comp_cant_cuota como null si la condición de pago es "CONTADO"
    if ($r->condicion_pago === 'CONTADO') {
        // Asegurar que estos campos sean null para pagos al contado
        $r->merge(['comp_intervalo_fecha_vence' => null, 'comp_cant_cuota' => null]);
    }
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence' => 'nullable|date',
            'comp_fecha'                 => 'nullable|date',
            'comp_estado'                => 'required',
            'comp_cant_cuota'            => 'nullable|integer',
            'condicion_pago'             => 'required',
            'comp_timbrado'              => 'nullable|string|max:20',
            'comp_nro_factura'           => ['nullable','string','max:15','regex:/^\d{3}-\d{3}-\d{7}$/'],
            'orden_compra_cab_id'        => 'required',
            'proveedor_id'               => 'required',
            'empresa_id'                 => 'required',
            'sucursal_id'                => 'required'
        ]);
        if ($r->condicion_pago === 'CONTADO') {
            $datosValidados['comp_intervalo_fecha_vence'] = null; // Establece null si es "CONTADO"
            $datosValidados['comp_cant_cuota'] = null; // Establece null si es "CONTADO"
        }
        $compracab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $compracab
        ],200);
    }

    public function anular(Request $r, $id) {
    // Buscar el registro por el ID
    $compracab = CompraCab::find($id);

    if (!$compracab) {
        return response()->json([
            'mensaje' => 'Compra no encontrada',
            'tipo'    => 'error',
        ], 404);
    }

    if ($r->comp_intervalo_fecha_vence === '') {
        $r->merge(['comp_intervalo_fecha_vence' => null]);
    }

    // Si la condición de pago es "CONTADO", limpiar campos
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge([
            'comp_intervalo_fecha_vence' => null,
            'comp_cant_cuota'            => null
        ]);
    }

    // Validar datos
    $datosValidados = $r->validate([
        'comp_intervalo_fecha_vence' => 'nullable|date',
        'comp_fecha'                 => 'nullable|date',
        'comp_estado'                => 'required',
        'comp_cant_cuota'            => 'nullable|integer',
        'condicion_pago'             => 'required',
        'comp_timbrado'              => 'nullable|string|max:20',
        'orden_compra_cab_id'        => 'required',
        'proveedor_id'               => 'required',
        'empresa_id'                 => 'required',
        'sucursal_id'                => 'required'
    ]);

    if ($r->condicion_pago === 'CONTADO') {
        $datosValidados['comp_intervalo_fecha_vence'] = null;
        $datosValidados['comp_cant_cuota']            = null;
    }

    // Guardar estado anterior
    $estadoAnterior = $compracab->comp_estado;

    // Actualizar compra a ANULADO
    $compracab->update($datosValidados);
    $compracab->comp_estado = "ANULADO";
    $compracab->save();

    // Inicializar mensaje según estado anterior
    if ($estadoAnterior === 'RECIBIDO') {

        // Libro de compras
        $libro = LibroCompras::where('compra_cab_id', $compracab->id)->first();
        if ($libro) {
            $libro->update([
                'libC_estado' => 'ANULADO',
                'updated_at'  => now()
            ]);
        }

        // Cuentas por pagar
        $ctasPagar = CtasPagar::where('compra_cab_id', $compracab->id)->get();
        foreach ($ctasPagar as $cuota) {
            $cuota->update([
                'cta_pag_estado' => 'Anulado',
                'updated_at'     => now()
            ]);
        }

        $detallesCompra = CompraDet::where('compra_cab_id', $compracab->id)->get();
        foreach ($detallesCompra as $detalle) {
            if (!$detalle->deposito_id) continue;

            $stock = DB::table('stock')
                ->where('deposito_id', $detalle->deposito_id)
                ->where('item_id', $detalle->item_id)
                ->first();
            if ($stock) {
                DB::table('stock')
                    ->where('deposito_id', $detalle->deposito_id)
                    ->where('item_id', $detalle->item_id)
                    ->update(['cantidad' => max(0, $stock->cantidad - $detalle->comp_det_cantidad), 'updated_at' => now()]);
            }
        }

        $mensaje = 'Registro anulado con éxito. Stock, Depósito, Libro de Compras y Cuentas por Pagar actualizados.';

    } else {
        // Si estaba PENDIENTE
        $mensaje = 'Registro anulado correctamente. La compra estaba pendiente, no se generaron movimientos de Stock, Libro de Compras ni Ctas por Pagar.';
    }

    // Revertir Orden de Compra a CONFIRMADO
    $ordencompracab = OrdenCompraCab::find($compracab->orden_compra_cab_id);
    if ($ordencompracab) {
        $ordencompracab->ord_comp_estado = 'CONFIRMADO';
        $ordencompracab->save();
    }

    return response()->json([
        'mensaje' => $mensaje,
        'tipo'    => 'success',
        'registro'=> $compracab
    ], 200);
}

public function calcularTotal(Request $r, $id)
{
    // Verificar si la compra existe
    $compracab = CompraCab::find($id);
    if (!$compracab) {
        return response()->json(['error' => 'Compra no encontrada.'], 404);
    }

    $compraCabId = $id;  // Aquí tomamos el ID de la compra

    // Modificar la consulta para usar bindings y evitar errores de sintaxis
    $detalles = DB::select("
        SELECT
            cd.comp_det_cantidad                              AS comp_det_cantidad,
            cd.comp_det_costo                                 AS comp_det_costo,
            ti.tip_imp_nom                                    AS tip_imp_nom,
            (cd.comp_det_cantidad * cd.comp_det_costo)        AS subtotal,
            CASE
                WHEN ti.tip_imp_nom = 'IVA10' THEN (cd.comp_det_cantidad * cd.comp_det_costo) / 11
                WHEN ti.tip_imp_nom = 'IVA5'  THEN (cd.comp_det_cantidad * cd.comp_det_costo) / 21
                ELSE 0
            END                                               AS iva_monto
        FROM compra_det cd
        JOIN items i          ON i.id  = cd.item_id
        JOIN tipo_impuesto ti  ON ti.id = cd.tipo_impuesto_id
        WHERE cd.compra_cab_id = :compra_cab_id
    ", ['compra_cab_id' => $compraCabId]);

    $totalGral = 0;
    $totalIVA  = 0;

    foreach ($detalles as $detalle) {
        $totalGral += $detalle->subtotal;
        $totalIVA  += $detalle->iva_monto;
    }

    // totalGral = precio total de compra (IVA incluido en el precio unitario)
    // totalIVA  = solo la porción impositiva (para el libro de compras)
    return response()->json([
        'totalGral'        => number_format($totalGral, 2),
        'totalConImpuesto' => number_format($totalIVA,  2)
    ]);
}

public function confirmar(Request $r, $id) {
    $compracab = CompraCab::find($id);

    if (!$compracab) {
        return response()->json(['error' => 'Compra no encontrada.'], 404);
    }

    // Ajustar valores en función de la condición de pago
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge([
            'comp_intervalo_fecha_vence' => null,
            'comp_cant_cuota' => null
        ]);
    } elseif ($r->comp_intervalo_fecha_vence === '') {
        $r->merge(['comp_intervalo_fecha_vence' => null]);
    }

    // Validar stock máximo ANTES de confirmar (sin guardar nada)
    $detallesPreview = CompraDet::where('compra_cab_id', $compracab->id)->get();
    foreach ($detallesPreview as $detalle) {
        if (!$detalle->deposito_id) continue;
        $stock = Stock::where('deposito_id', $detalle->deposito_id)
                      ->where('item_id', $detalle->item_id)
                      ->first();
        if ($stock && $stock->cantidad_maxima > 0) {
            $nuevaCantidad = $stock->cantidad + $detalle->comp_det_cantidad;
            if ($nuevaCantidad > $stock->cantidad_maxima) {
                return response()->json([
                    'mensaje' => "No se puede confirmar: el ítem ID {$detalle->item_id} superaría el stock máximo ({$stock->cantidad_maxima}) en el depósito {$detalle->deposito_id}. Cantidad actual: {$stock->cantidad}, cantidad a ingresar: {$detalle->comp_det_cantidad}.",
                    'tipo'    => 'error'
                ], 400);
            }
        }
    }

    DB::beginTransaction();
    try {

    // Validaciones y preparación de datos
    $datosValidados = $r->validate([
        'comp_intervalo_fecha_vence' => 'nullable|date',
        'comp_fecha'                 => 'nullable|date',
        'comp_estado'                => 'required',
        'comp_cant_cuota'            => 'nullable|integer',
        'condicion_pago'             => 'required',
        'comp_timbrado'              => 'nullable|string|max:20',
        'orden_compra_cab_id'        => 'required',
        'proveedor_id'               => 'required',
        'empresa_id'                 => 'required',
        'sucursal_id'                => 'required'
    ]);

    // Actualizar estado de la compra a "RECIBIDO"
    $compracab->update($datosValidados);
    $compracab->comp_estado = "RECIBIDO";
    $compracab->save();

    // Calcular totales: totalGral = monto a pagar; totalConImpuesto = solo la porción IVA
    $resultadoCalculo  = $this->calcularTotal($r, $id);
    $totalGral         = (float) str_replace(',', '', $resultadoCalculo->getData()->totalGral);
    $totalConImpuesto  = (float) str_replace(',', '', $resultadoCalculo->getData()->totalConImpuesto);

    // CtasPagar se basa en el precio total de compra (totalGral)
    $cuotas     = ($compracab->condicion_pago === 'CONTADO') ? 1 : max(1, (int)($compracab->comp_cant_cuota ?? 1));
    $montoCuota = round($totalGral / $cuotas, 2);
    $fechaBase  = $compracab->comp_fecha ?? now();

    for ($i = 1; $i <= $cuotas; $i++) {
        $fechaVencimiento = ($compracab->condicion_pago === 'CONTADO')
            ? $fechaBase
            : \Carbon\Carbon::parse($fechaBase)->addMonths($i);

        CtasPagar::create([
            'compra_cab_id'  => $compracab->id,
            'nro_cuota'      => $i,
            'cta_pag_monto'  => $montoCuota,
            'cta_pag_fecha'  => $fechaVencimiento,
            'cta_pag_cuota'  => $cuotas,
            'cta_pag_estado' => ($compracab->condicion_pago === 'CONTADO') ? 'Pagada' : 'Pendiente',
            'condicion_pago' => $compracab->condicion_pago,
        ]);
    }

    $detallesCompra = CompraDet::where('compra_cab_id', $compracab->id)->get();

    foreach ($detallesCompra as $detalle) {
        if (!$detalle->deposito_id) continue;

        $stock = DB::table('stock')
            ->where('deposito_id', $detalle->deposito_id)
            ->where('item_id', $detalle->item_id)
            ->first();

        if ($stock) {
            DB::table('stock')
                ->where('deposito_id', $detalle->deposito_id)
                ->where('item_id', $detalle->item_id)
                ->update(['cantidad' => $stock->cantidad + $detalle->comp_det_cantidad, 'updated_at' => now()]);
        } else {
            DB::table('stock')->insert([
                'deposito_id'     => $detalle->deposito_id,
                'item_id'         => $detalle->item_id,
                'cantidad'        => $detalle->comp_det_cantidad,
                'cantidad_minima' => 0,
                'cantidad_maxima' => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    // Obtener el tipo de impuesto desde el detalle de compra
    $compraDet = CompraDet::where('compra_cab_id', $compracab->id)->first();

    if (!$compraDet) {
        return response()->json(['error' => 'Detalle de compra no encontrado.'], 404);
    }

    // Obtener datos adicionales del proveedor
    $proveedor = Proveedor::find($r->proveedor_id);
    $provRazonSocial = $proveedor ? $proveedor->prov_razonsocial : null;
    $provRuc = $proveedor ? $proveedor->prov_ruc : null;

    // Obtener nombre del tipo de impuesto
    $tipoImpuestoObj = TipoImpuesto::find($compraDet->tipo_impuesto_id);
    $tipoImpuestoNombre = $tipoImpuestoObj ? $tipoImpuestoObj->tip_imp_nom : null;

    // Insertar el registro en libro_compras con los nuevos campos
        LibroCompras::create([
        'compra_cab_id'   => $compracab->id,
        'libC_monto'      => $totalGral,
        'libC_fecha'      => now(),
        'libC_cuota'      => $r->comp_cant_cuota ?? 1,
        'libC_tipo_nota'  => $r->libC_tipo_nota,
        'proveedor_id'    => $r->proveedor_id,
        'prov_razonsocial'=> $provRazonSocial,
        'prov_ruc'        => $provRuc,
        'tipo_impuesto_id'=> $compraDet->tipo_impuesto_id,
        'tip_imp_nom'     => $tipoImpuestoNombre,
        'condicion_pago'  => $r->condicion_pago,
        'libC_estado'     => 'ACTIVO',   
        'updated_at'      => now(),
        'created_at'      => now(),
    ]);


    DB::commit();

    return response()->json([
        'mensaje'  => 'Compra registrada con éxito. Cuenta por pagar, Libro de Compras, Stock y Depósito actualizados.',
        'tipo'     => 'success',
        'registro' => $compracab,
    ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['mensaje' => 'Error al confirmar la compra. Intente nuevamente.', 'tipo' => 'error'], 500);
    }
}
public function buscar(Request $r)
{
    $funcId   = $r->input('funcionario_id');
    $funcName = $r->input('name');

    return DB::select("
        SELECT
            cc.id AS compra_cab_id,
            TO_CHAR(cc.comp_fecha, 'YYYY-MM-DD HH:mm:ss') AS comp_fecha,
            COALESCE(to_char(cc.comp_intervalo_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') AS comp_intervalo_fecha_vence,
            cc.comp_estado,
            cc.condicion_pago,
            COALESCE(cc.comp_cant_cuota::varchar, '0') AS comp_cant_cuota,
            cc.sucursal_id,
            s.suc_razon_social AS suc_razon_social,
            cc.empresa_id,
            e.emp_razon_social AS emp_razon_social,
            cc.funcionario_id,
            cc.created_at,
            cc.updated_at,
            f.fun_nom || ' ' || f.fun_apellido AS encargado,
            cc.proveedor_id,
            prov.prov_razonsocial,
            prov.prov_ruc,
            prov.prov_telefono,
            prov.prov_correo,
            'COMPRA NRO: ' || TO_CHAR(cc.id, '0000000') || ' VENCE EL: ' || TO_CHAR(cc.comp_fecha, 'YYYY-MM-DD HH:mm:ss') AS compra,
            COALESCE(to_char(cc.comp_intervalo_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') as nota_comp_intervalo_fecha_vence,
            COALESCE(cc.comp_cant_cuota::varchar, '0') as nota_comp_cantidad_cuota,
            COALESCE(cc.comp_timbrado, '') AS comp_timbrado,
            COALESCE(cc.comp_nro_factura, '') AS comp_nro_factura,
            COALESCE(TO_CHAR(cc.comp_fecha_emision, 'DD/MM/YYYY'), '') AS comp_fecha_emision
        FROM
            compra_cab cc
        JOIN
            funcionario f ON f.id = cc.funcionario_id
        JOIN
            sucursal s ON s.id = cc.sucursal_id
        JOIN
            empresa e ON e.id = cc.empresa_id
        JOIN
            proveedores prov ON prov.id = cc.proveedor_id
        WHERE
            cc.comp_estado = 'RECIBIDO'
        AND
            cc.funcionario_id = ?
        AND
            (f.fun_nom || ' ' || f.fun_apellido) ILIKE ?
    ", [$funcId, '%' . $funcName . '%']);
}
public function buscarInforme(Request $r)
{
    $desde = $r->query('desde');
    $hasta = $r->query('hasta');

    return DB::select("
        SELECT 
            cc.id,
            TO_CHAR(cc.comp_fecha, 'dd/mm/yyyy') AS fecha,
            CASE 
                WHEN cc.comp_intervalo_fecha_vence IS NOT NULL 
                THEN TO_CHAR(cc.comp_intervalo_fecha_vence, 'dd/mm/yyyy')
                ELSE 'N/A'
            END AS entrega,
            cc.comp_estado AS estado,
            cc.condicion_pago,
            COALESCE(cc.comp_cant_cuota::varchar, '0') AS cuotas,
            f.fun_nom || ' ' || f.fun_apellido AS funcionario,
            s.suc_razon_social AS sucursal,
            e.emp_razon_social AS empresa,
            prov.prov_razonsocial AS proveedor,
            prov.prov_ruc AS ruc,
            'ORDEN DE COMPRA NRO: ' || TO_CHAR(occ.id, '0000000') ||
            CASE
                WHEN occ.ord_comp_intervalo_fecha_vence IS NOT NULL
                THEN ' VENCE EL: ' || TO_CHAR(occ.ord_comp_intervalo_fecha_vence, 'YYYY-MM-DD HH24:MI:SS')
                ELSE ' N/A'
            END AS ordencompra
        FROM compra_cab cc
        JOIN funcionario f ON f.id = cc.funcionario_id
        JOIN sucursal s ON s.id = cc.sucursal_id
        JOIN empresa e ON e.id = cc.empresa_id
        LEFT JOIN orden_compra_cab occ ON occ.id = cc.orden_compra_cab_id
        LEFT JOIN presupuestos p ON p.id = occ.presupuesto_id
        LEFT JOIN proveedores prov ON prov.id = p.proveedor_id
        WHERE cc.comp_estado = 'PROCESADO'
            AND cc.comp_fecha BETWEEN ? AND ?
        ORDER BY cc.comp_fecha ASC
    ", [$desde, $hasta]);
}

}