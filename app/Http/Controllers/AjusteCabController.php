<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AjusteCab;
use App\Models\AjusteDet;
use App\Models\Stock;

class AjusteCabController extends Controller
{
    public function read() {
        return DB::select("SELECT 
            ac.id,
            TO_CHAR(ac.ajus_cab_fecha, 'dd/mm/yyyy HH24:mi:ss') AS ajus_cab_fecha,
            ac.ajus_cab_estado,
            ac.tipo_ajuste,
            ac.sucursal_id,
            s.suc_razon_social AS suc_razon_social,
            ac.empresa_id,
            e.emp_razon_social AS emp_razon_social,
            ac.user_id,
            ac.motivo_ajuste_id,
            ma.descripcion AS descripcion,
            ac.created_at,
            ac.updated_at,
            u.name
        FROM ajuste_cab ac
        JOIN sucursal s ON s.empresa_id = ac.sucursal_id
        JOIN empresa e ON e.id = ac.empresa_id
        JOIN users u ON u.id = ac.user_id
        JOIN motivo_ajuste ma ON ma.id = ac.motivo_ajuste_id;");
    }

    public function store(Request $r){
        $datosValidados = $r->validate([
            'ajus_cab_fecha' => 'required',
            'ajus_cab_estado' => 'required',
            'tipo_ajuste' => 'required',
            'user_id' => 'required',
            'empresa_id' => 'required',
            'sucursal_id' => 'required',
            'motivo_ajuste_id' => 'required'
        ]);

        $ajustecab = AjusteCab::create($datosValidados);
        $ajustecab->save();

        return response()->json([
            'mensaje' => 'Registro creado con éxito',
            'tipo' => 'success',
            'registro' => $ajustecab
        ], 200);
    }

    public function update(Request $r, $id){
        $ajustecab = AjusteCab::find($id);
        if (!$ajustecab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $r->validate([
            'ajus_cab_fecha' => 'required',
            'ajus_cab_estado' => 'required',
            'tipo_ajuste' => 'required',
            'user_id' => 'required',
            'empresa_id' => 'required',
            'sucursal_id' => 'required',
            'motivo_ajuste_id' => 'required'
        ]);

        $ajustecab->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => $ajustecab
        ], 200);
    }

    public function anular(Request $r, $id)
{
    $ajustecab = AjusteCab::find($id);
    if (!$ajustecab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    $datosValidados = $r->validate([
        'ajus_cab_fecha' => 'required',
        'ajus_cab_estado' => 'required',
        'tipo_ajuste' => 'required',
        'user_id' => 'required',
        'empresa_id' => 'required',
        'sucursal_id' => 'required',
        'motivo_ajuste_id' => 'required'
    ]);

    // Guardar estado anterior
    $estadoAnterior = $ajustecab->ajus_cab_estado;

    // Actualizamos el registro con los datos
    $ajustecab->update($datosValidados);

    // Marcamos como ANULADO
    $ajustecab->ajus_cab_estado = 'ANULADO';
    $ajustecab->save();

    // Si el ajuste estaba CONFIRMADO → revertimos el stock
    if ($estadoAnterior === 'CONFIRMADO') {
        $ajusteDetalles = AjusteDet::where('ajuste_cab_id', $ajustecab->id)->get();

        foreach ($ajusteDetalles as $detalle) {
            $stock = Stock::where('item_id', $detalle->item_id)->first();

            if ($stock) {
                if ($ajustecab->tipo_ajuste === 'Entrada') {
                    // Revertir una entrada → restar
                    if ($stock->cantidad < $detalle->ajus_det_cantidad) {
                        return response()->json([
                            'mensaje' => "No se puede anular. El stock del item {$detalle->item_id} es insuficiente para revertir la entrada.",
                            'tipo' => 'error'
                        ], 400);
                    }
                    $stock->cantidad -= $detalle->ajus_det_cantidad;
                } elseif ($ajustecab->tipo_ajuste === 'Salida') {
                    // Revertir una salida → sumar
                    $stock->cantidad += $detalle->ajus_det_cantidad;
                }
                $stock->save();
            }
        }
    }

    return response()->json([
        'mensaje' => $estadoAnterior === 'CONFIRMADO'
            ? 'Ajuste confirmado anulado con éxito y stock revertido.'
            : 'Ajuste pendiente anulado con éxito.',
        'tipo' => 'success',
        'registro' => $ajustecab
    ], 200);
}


    public function confirmar(Request $r, $id) {
    \DB::beginTransaction();
    try {
        // Buscar cabecera
        $ajustecab = AjusteCab::find($id);
        if (!$ajustecab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        // Validar entrada (igual que antes)
        $datosValidados = $r->validate([
            'ajus_cab_fecha'     => 'required',
            'ajus_cab_estado'    => 'required',
            'tipo_ajuste'        => 'required', // valores esperados: 'Entrada' o 'Salida'
            'user_id'            => 'required',
            'empresa_id'         => 'required',
            'sucursal_id'        => 'required',
            'motivo_ajuste_id'   => 'required'
        ]);

        // Si ya está confirmado, no hacer nada
        if ($ajustecab->ajus_cab_estado === 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'El ajuste ya fue confirmado anteriormente.',
                'tipo'    => 'warning'
            ], 400);
        }

        // Actualizamos la cabecera con los datos enviados (opcional)
        $ajustecab->update($datosValidados);

        // Obtener los detalles del ajuste (si no definiste relación, usamos el modelo)
        $ajusteDetalles = AjusteDet::where('ajuste_cab_id', $ajustecab->id)->get();

        if ($ajusteDetalles->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se encontraron detalles para este ajuste.',
                'tipo' => 'error'
            ], 400);
        }

        // Recorremos y aplicamos los cambios al stock según tipo_ajuste
        foreach ($ajusteDetalles as $detalle) {
            // cantidad en detalle se espera positiva
            $cantidad = (float) $detalle->ajus_det_cantidad;
            if ($cantidad <= 0) {
                return response()->json([
                    'mensaje' => 'Cantidad inválida en detalle para el item: ' . $detalle->item_id,
                    'tipo' => 'error'
                ], 400);
            }

            $stock = Stock::where('item_id', $detalle->item_id)->first();
            if (!$stock) {
                return response()->json([
                    'mensaje' => 'El item ' . $detalle->item_id . ' no tiene stock registrado.',
                    'tipo' => 'error'
                ], 400);
            }

            if ($ajustecab->tipo_ajuste === 'Entrada') {
                // Sumar siempre
                $nuevaCantidad = $stock->cantidad + $cantidad;

                // Validación de tope (mantengo tu regla: máximo 30)
                if ($nuevaCantidad > 30) {
                    return response()->json([
                        'mensaje' => 'La cantidad en stock no puede superar el límite máximo de 30 para el item: ' . $detalle->item_id,
                        'tipo' => 'error'
                    ], 400);
                }

                $stock->cantidad = $nuevaCantidad;
                $stock->save();
            }
            elseif ($ajustecab->tipo_ajuste === 'Salida') {
                // Restar siempre
                if ($stock->cantidad < $cantidad) {
                    return response()->json([
                        'mensaje' => 'No hay suficiente stock para realizar la salida del item: ' . $detalle->item_id,
                        'tipo' => 'error'
                    ], 400);
                }

                $stock->cantidad = $stock->cantidad - $cantidad;
                $stock->save();
            }
            else {
                return response()->json([
                    'mensaje' => 'Tipo de ajuste desconocido: ' . $ajustecab->tipo_ajuste,
                    'tipo' => 'error'
                ], 400);
            }
        }

        // Finalmente marcamos la cabecera como CONFIRMADO (independientemente de lo que vino en la request)
        $ajustecab->ajus_cab_estado = 'CONFIRMADO';
        $ajustecab->save();

        \DB::commit();
        return response()->json([
            'mensaje' => 'Ajuste confirmado con éxito y stock actualizado.',
            'tipo' => 'success',
            'registro' => $ajustecab
        ], 200);

    } catch (\Exception $e) {
        \DB::rollBack();
        return response()->json([
            'mensaje' => 'Error al confirmar el ajuste de inventario: ' . $e->getMessage(),
            'tipo' => 'error'
        ], 500);
    }
}
        
    public function buscarInforme(Request $r)
{
    $desde = $r->query('desde');
    $hasta = $r->query('hasta');

    return DB::select("
        SELECT 
            ac.id,
            TO_CHAR(ac.ajus_cab_fecha, 'dd/mm/yyyy') AS fecha,
            ac.ajus_cab_estado AS estado,
            ac.tipo_ajuste AS tipo,
            u.name AS encargado,
            s.suc_razon_social AS sucursal,
            e.emp_razon_social AS empresa,
            ma.descripcion AS motivo
        FROM ajuste_cab ac
        JOIN users u ON u.id = ac.user_id
        JOIN sucursal s ON s.empresa_id = ac.sucursal_id
        JOIN empresa e ON e.id = ac.empresa_id
        JOIN motivo_ajuste ma ON ma.id = ac.motivo_ajuste_id
        WHERE ac.ajus_cab_estado = 'CONFIRMADO'
        AND ac.ajus_cab_fecha BETWEEN ? AND ?
        ORDER BY ac.ajus_cab_fecha ASC
    ", [$desde, $hasta]);
}
}
