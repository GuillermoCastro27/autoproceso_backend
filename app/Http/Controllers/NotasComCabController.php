<?php

namespace App\Http\Controllers;

use App\Models\NotaCompCab;
use App\Models\NotaCompDet;
use App\Models\CompraCab;
use App\Models\CtasPagar;
use App\Models\Stock;
use App\Models\LibroCompras;
use App\Models\Proveedor;
use App\Models\TipoImpuesto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class NotasComCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
    ncc.id,
    p.id AS proveedor_id,
    ncc.empresa_id,
    ncc.sucursal_id,
    ncc.compra_cab_id,
    COALESCE('COMPRA NRO: ' || to_char(cc.id, '0000000'), 'SIN COMPRA') AS compra,
    COALESCE(to_char(ncc.nota_comp_intervalo_fecha_vence, 'YYYY-MM-DD HH24:MI:SS'), 'N/A') AS nota_comp_intervalo_fecha_vence,
    COALESCE(to_char(ncc.nota_comp_fecha, 'YYYY-MM-DD HH24:MI:SS'), 'N/A') AS nota_comp_fecha,
    ncc.nota_comp_estado,
    COALESCE(ncc.nota_comp_cant_cuota::varchar, '0') AS nota_comp_cant_cuota,
    p.prov_razonsocial AS prov_razonsocial,
    p.prov_ruc AS prov_ruc,
    p.prov_telefono AS prov_telefono,
    p.prov_correo AS prov_correo,
    ncc.nota_comp_tipo,
    ncc.nota_comp_afecta_stock,
    ncc.nota_comp_observaciones,
    COALESCE(ncc.nota_comp_timbrado, '')   AS nota_comp_timbrado,
    COALESCE(ncc.nota_comp_nro_nota, '')  AS nota_comp_nro_nota,
    COALESCE(cc.comp_timbrado, '')        AS comp_timbrado,
    COALESCE(cc.comp_nro_factura, '')     AS comp_nro_factura,
    ncc.nota_comp_condicion_pago,
    f.fun_nom || ' ' || f.fun_apellido AS funcionario,
    s.suc_razon_social AS suc_razon_social,
    e.emp_razon_social AS emp_razon_social,
    ncc.created_at,
    ncc.updated_at
FROM
    notas_comp_cab ncc
JOIN funcionario f ON f.id = ncc.funcionario_id
JOIN
    sucursal s ON s.id = ncc.sucursal_id
JOIN 
    empresa e ON e.id = ncc.empresa_id
LEFT JOIN 
    compra_cab cc ON cc.id = ncc.compra_cab_id
LEFT JOIN 
    proveedores p ON p.id = cc.proveedor_id
    ");
}

public function store(Request $r) {
    // Convertir cadenas vacías a null
    if ($r->nota_comp_intervalo_fecha_vence === '') {
        $r->merge(['nota_comp_intervalo_fecha_vence' => null]);
    }

    if ($r->nota_comp_condicion_pago === 'CONTADO') {
        $r->merge(['nota_comp_intervalo_fecha_vence' => null, 'nota_comp_cant_cuota' => null]);
    }

    $datosValidados = $r->validate([
        'nota_comp_intervalo_fecha_vence' => 'nullable|date',
        'nota_comp_fecha' => 'required|date',
        'nota_comp_estado' => 'required',
        'nota_comp_cant_cuota' => 'nullable|integer',
        'nota_comp_tipo'           => 'required',
        'nota_comp_afecta_stock'   => 'nullable|boolean',
        'nota_comp_observaciones'  => 'required',
        'nota_comp_timbrado'       => 'nullable|string|max:20',
        'nota_comp_nro_nota'       => ['nullable','string','max:15','regex:/^\d{3}-\d{3}-\d{7}$/'],
        'funcionario_id'           => 'nullable',
        'compra_cab_id'            => 'required|integer',
        'empresa_id'               => 'required|integer',
        'sucursal_id'              => 'required|integer',
        'nota_comp_condicion_pago' => 'required|string|max:20'
    ]);

    $datosValidados['funcionario_id'] = auth()->user()->funcionario_id;
    // Crear cabecera
    $notaCompCab = NotaCompCab::create($datosValidados);

    // Actualizar estado de la compra
    $compracab = CompraCab::find($r->compra_cab_id);
    if ($compracab) {
        $compracab->comp_estado = "PROCESADO";
        $compracab->save();

        // Copiar detalles de compra a nota de compra
        $detalles = DB::table('compra_det')
        ->where('compra_cab_id', $compracab->id)
        ->get();

        foreach ($detalles as $detalle) {
            NotaCompDet::create([
                'notas_comp_cab_id'      => $notaCompCab->id,
                'item_id'                => $detalle->item_id,
                'tipo_impuesto_id'       => $detalle->tipo_impuesto_id,
                'notas_comp_det_cantidad'=> $detalle->comp_det_cantidad,
                'notas_comp_det_costo'   => $detalle->comp_det_costo,
                'deposito_id'            => $detalle->deposito_id,
            ]);
        }
    }

    return response()->json([
        'mensaje' => 'Registro creado con éxito',
        'tipo' => 'success',
        'registro' => $notaCompCab
    ], 201);
}

public function update(Request $r, $id)
{
    $notacompcab = NotaCompCab::find($id);
    if (!$notacompcab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }
     // Convertir cadena vacía a null antes de la validación
    if ($r->nota_comp_intervalo_fecha_vence === '') {
        $r->merge(['nota_comp_intervalo_fecha_vence' => null]);
    }

    // Establecer ord_comp_cant_cuota como null si la condición de pago es "CONTADO"
    if ($r->nota_comp_condicion_pago === 'CONTADO') {
        $r->merge(['nota_comp_intervalo_fecha_vence' => null, 'nota_comp_cant_cuota' => null]);
    }


    $datosValidados = $r->validate([
        'nota_comp_intervalo_fecha_vence' => 'nullable|date',
        'nota_comp_fecha' => 'required|date',
        'nota_comp_estado' => 'required',
        'nota_comp_cant_cuota' => 'nullable|integer',
        'nota_comp_tipo' => 'required',
        'nota_comp_observaciones' => 'required',
        'nota_comp_timbrado'  => 'nullable|string|max:20',
        'nota_comp_nro_nota'  => ['nullable','string','max:15','regex:/^\d{3}-\d{3}-\d{7}$/'],
        'compra_cab_id'       => 'required|integer',
        'empresa_id'          => 'required|integer',
        'sucursal_id'         => 'required|integer',
        'nota_comp_condicion_pago' => 'required|string|max:20'
    ]);

    if ($r->nota_comp_condicion_pago === 'CONTADO') {
        $datosValidados['nota_comp_intervalo_fecha_vence'] = null;
        $datosValidados['nota_comp_cant_cuota'] = null;
    }

    $notacompcab->update($datosValidados);
    
    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success',
        'registro' => $notacompcab
    ], 200);
}
public function anular(Request $r, $id)
{
    $notacompcab = NotaCompCab::find($id);

    if (!$notacompcab) {
        return response()->json([
            'mensaje' => 'Nota de compra no encontrada',
            'tipo'    => 'error',
        ], 404);
    }

    // Guardamos estado previo
    $estadoAnterior = $notacompcab->nota_comp_estado;

    // Validar datos
    $datosValidados = $r->validate([
        'nota_comp_intervalo_fecha_vence' => 'nullable|date',
        'nota_comp_fecha'                 => 'required|date',
        'nota_comp_estado'                => 'required',
        'nota_comp_cant_cuota'            => 'nullable|integer',
        'nota_comp_tipo'                  => 'required',
        'nota_comp_observaciones'         => 'required',
        'nota_comp_timbrado'              => 'nullable|string|max:20',
        'funcionario_id'                  => 'nullable|integer',
        'compra_cab_id'                   => 'required|integer',
        'empresa_id'                      => 'required|integer',
        'sucursal_id'                     => 'required|integer',
        'nota_comp_condicion_pago'        => 'required|string|max:20'
    ]);

    // Actualizamos cabecera
    $notacompcab->update($datosValidados);
    $notacompcab->nota_comp_estado = "ANULADO";
    $notacompcab->save();

    // Solo revertimos si estaba CONFIRMADO
    if ($estadoAnterior === 'CONFIRMADO') {
        $tipoNota = trim($notacompcab->nota_comp_tipo); // Crédito o Débito

        // Calcular total de la nota para revertir
        $detallesParaMonto = NotaCompDet::where('notas_comp_cab_id', $notacompcab->id)->get();
        $totalNota = 0;
        foreach ($detallesParaMonto as $det) {
            $totalNota += $det->notas_comp_det_cantidad * $det->notas_comp_det_costo;
        }

        // Revertir Libro de Compras
        $libro = LibroCompras::where('compra_cab_id', $notacompcab->compra_cab_id)->first();
        if ($libro && $totalNota > 0) {
            $libro->libC_monto = $tipoNota === 'Crédito'
                ? $libro->libC_monto + $totalNota   // NC había restado → devolvemos
                : max(0, $libro->libC_monto - $totalNota); // ND había sumado → quitamos
            $libro->libC_tipo_nota = null;
            $libro->update(['libC_estado' => 'ACTIVO', 'libC_monto' => $libro->libC_monto, 'libC_tipo_nota' => null]);
        }

        // Revertir Cuentas a Pagar: distribuir entre todas las cuotas
        $cuotas = CtasPagar::where('compra_cab_id', $notacompcab->compra_cab_id)->get();
        if ($cuotas->isNotEmpty() && $totalNota > 0) {
            $ajustePorCuota = $totalNota / $cuotas->count();
            foreach ($cuotas as $cuota) {
                $cuota->cta_pag_monto = $tipoNota === 'Crédito'
                    ? $cuota->cta_pag_monto + $ajustePorCuota   // NC había restado → devolvemos
                    : max(0, $cuota->cta_pag_monto - $ajustePorCuota); // ND había sumado → quitamos
                $cuota->save();
            }
        }

        // Revertir stock solo si la nota afectaba stock
        if ($notacompcab->nota_comp_afecta_stock) {
            $detallesNota = NotaCompDet::where('notas_comp_cab_id', $notacompcab->id)->get();
            foreach ($detallesNota as $detalle) {
                if (!$detalle->deposito_id) continue;
                $cantidad = $detalle->notas_comp_det_cantidad;
                if ($tipoNota === 'Debito') {
                    $this->restarDeStock($detalle->deposito_id, $detalle->item_id, $cantidad);
                } elseif ($tipoNota === 'Crédito') {
                    $this->agregarAlStock($detalle->deposito_id, $detalle->item_id, $cantidad);
                }
            }
        }
    }

    // Revertir Compra a RECIBIDO
    $compracab = CompraCab::find($notacompcab->compra_cab_id);
    if ($compracab) {
        $compracab->comp_estado = 'RECIBIDO';
        $compracab->save();
    }

    return response()->json([
        'mensaje' => $estadoAnterior === 'CONFIRMADO'
            ? 'Nota de compra confirmada fue anulada y los cambios revertidos.'
            : 'Nota de compra anulada con éxito.',
        'tipo'    => 'success',
        'registro'=> $notacompcab
    ], 200);
}

    public function confirmar(Request $r, $id) 
{
    $notacompcab = NotaCompCab::find($id);

    if (!$notacompcab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    if ($r->nota_comp_intervalo_fecha_vence === '') {
        $r->merge(['nota_comp_intervalo_fecha_vence' => null]);
    }

    if ($r->nota_comp_condicion_pago === 'CONTADO') {
        $r->merge([
            'nota_comp_intervalo_fecha_vence' => null,
            'nota_comp_cant_cuota' => null
        ]);
    }

    $datosValidados = $r->validate([
        'nota_comp_intervalo_fecha_vence' => 'nullable|date',
        'nota_comp_fecha' => 'required|date',
        'nota_comp_estado' => 'required',
        'nota_comp_cant_cuota' => 'nullable|integer',
        'nota_comp_tipo' => 'required',
        'nota_comp_observaciones' => 'required',
        'nota_comp_timbrado' => 'nullable|string|max:20',
        'compra_cab_id' => 'required|integer',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'nota_comp_condicion_pago' => 'required|string|max:20'
    ]);

    DB::beginTransaction();

    try {
        $notacompcab->nota_comp_estado = 'CONFIRMADO';
        $notacompcab->update($datosValidados);
        $notacompcab->save();

        $tipoNota = trim($notacompcab->nota_comp_tipo); // "Crédito" o "Debito"

        if (in_array($tipoNota, ['Crédito', 'Debito'])) {
            $LibroCompras = LibroCompras::where('compra_cab_id', $notacompcab->compra_cab_id)->first();
            $CtasPagar = CtasPagar::where('compra_cab_id', $notacompcab->compra_cab_id)->first();

            if (!$LibroCompras || !$CtasPagar) {
                return response()->json([
                    'mensaje' => 'Faltan registros relacionados: Libro de compras o Cuenta por pagar no encontrados',
                    'tipo' => 'error'
                ], 404);
            }

            $detalles = NotaCompDet::where('notas_comp_cab_id', $notacompcab->id)->get();

            // Calcular total de la nota (monto completo de los ítems)
            $totalNota = 0;
            foreach ($detalles as $detalle) {
                $totalNota += $detalle->notas_comp_det_cantidad * $detalle->notas_comp_det_costo;
            }

            // Mover stock solo si la nota lo indica
            if ($notacompcab->nota_comp_afecta_stock) {
                foreach ($detalles as $detalle) {
                    if (!$detalle->deposito_id) continue;
                    if ($tipoNota === 'Debito') {
                        $this->agregarAlStock($detalle->deposito_id, $detalle->item_id, $detalle->notas_comp_det_cantidad);
                    } elseif ($tipoNota === 'Crédito') {
                        $this->restarDeStock($detalle->deposito_id, $detalle->item_id, $detalle->notas_comp_det_cantidad);
                    }
                }
            }

            // Ajustar Libro de Compras: solo cambia el monto y registra el tipo de nota
            $LibroCompras->libC_tipo_nota = $tipoNota === 'Crédito' ? 'NC' : 'ND';
            $LibroCompras->libC_monto = $tipoNota === 'Crédito'
                ? max(0, $LibroCompras->libC_monto - $totalNota)
                : $LibroCompras->libC_monto + $totalNota;
            $LibroCompras->save();

            // Ajustar Cuentas a Pagar: distribuir el ajuste entre todas las cuotas
            $cuotas = CtasPagar::where('compra_cab_id', $notacompcab->compra_cab_id)->get();
            if ($cuotas->isNotEmpty() && $totalNota > 0) {
                $ajustePorCuota = $totalNota / $cuotas->count();
                foreach ($cuotas as $cuota) {
                    $cuota->cta_pag_monto = $tipoNota === 'Crédito'
                        ? max(0, $cuota->cta_pag_monto - $ajustePorCuota)
                        : $cuota->cta_pag_monto + $ajustePorCuota;
                    $cuota->save();
                }
            }
        }

        DB::commit();

        return response()->json([
            'mensaje' => 'Nota de compra confirmada con éxito',
            'tipo' => 'success',
            'registro' => $notacompcab
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'mensaje' => 'Error al confirmar nota de compra: ' . $e->getMessage(),
            'tipo' => 'error'
        ], 500);
    }
}
protected function agregarAlStock($depositoId, $itemId, $cantidad)
{
    $stock = DB::table('stock')
        ->where('deposito_id', $depositoId)
        ->where('item_id', $itemId)
        ->first();

    if ($stock) {
        DB::table('stock')
            ->where('deposito_id', $depositoId)
            ->where('item_id', $itemId)
            ->update(['cantidad' => $stock->cantidad + $cantidad, 'updated_at' => now()]);
    } else {
        DB::table('stock')->insert([
            'deposito_id'     => $depositoId,
            'item_id'         => $itemId,
            'cantidad'        => $cantidad,
            'cantidad_minima' => 0,
            'cantidad_maxima' => 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }
}

protected function restarDeStock($depositoId, $itemId, $cantidad)
{
    $stock = DB::table('stock')
        ->where('deposito_id', $depositoId)
        ->where('item_id', $itemId)
        ->first();

    if ($stock) {
        DB::table('stock')
            ->where('deposito_id', $depositoId)
            ->where('item_id', $itemId)
            ->update(['cantidad' => max(0, $stock->cantidad - $cantidad), 'updated_at' => now()]);
    }
}
public function buscarInforme(Request $r)
{
    $desde = $r->query('desde');
    $hasta = $r->query('hasta');

    return DB::select("
        SELECT 
            ncc.id,
            TO_CHAR(ncc.nota_comp_fecha, 'dd/mm/yyyy') AS fecha,
            COALESCE(TO_CHAR(ncc.nota_comp_intervalo_fecha_vence, 'dd/mm/yyyy'), 'N/A') AS entrega,
            ncc.nota_comp_tipo AS tipo,
            ncc.nota_comp_observaciones AS observaciones,
            ncc.nota_comp_estado AS estado,
            ncc.nota_comp_condicion_pago AS condicion_pago,
            COALESCE(ncc.nota_comp_cant_cuota::varchar, '0') AS cuotas,
            f.fun_nom || ' ' || f.fun_apellido AS funcionario,
            s.suc_razon_social AS sucursal,
            e.emp_razon_social AS empresa,
            COALESCE(p.prov_razonsocial, 'SIN PROVEEDOR') AS proveedor,
            COALESCE(p.prov_ruc, 'SIN RUC') AS ruc,
            COALESCE('COMPRA NRO: ' || TO_CHAR(cc.id, '0000000'), 'SIN COMPRA') AS compra
        FROM notas_comp_cab ncc
        JOIN funcionario f ON f.id = ncc.funcionario_id
        JOIN sucursal s ON s.id = ncc.sucursal_id
        JOIN empresa e ON e.id = ncc.empresa_id
        LEFT JOIN compra_cab cc ON cc.id = ncc.compra_cab_id
        LEFT JOIN proveedores p ON p.id = cc.proveedor_id
        WHERE ncc.nota_comp_estado = 'CONFIRMADO'
            AND ncc.nota_comp_fecha BETWEEN ? AND ?
        ORDER BY ncc.nota_comp_fecha ASC
    ", [$desde, $hasta]);
}


}
