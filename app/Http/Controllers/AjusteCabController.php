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

    public function anular(Request $r, $id){
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
            'mensaje' => 'Registro anulado con éxito',
            'tipo' => 'success',
            'registro' => $ajustecab
        ], 200);
    }

    public function confirmar(Request $r, $id) {
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
    
        // Obtener el detalle del ajuste usando el nombre correcto de la columna
        $ajusteDetalles = AjusteDet::where('ajuste_cab_id', $ajustecab->id)->get();
    
        foreach ($ajusteDetalles as $detalle) {
            // Obtener el stock actual del item
            $stock = Stock::where('item_id', $detalle->item_id)->first();
    
            if ($stock) {
                // Validación según el tipo de ajuste
                if ($ajustecab->tipo_ajuste === 'Entrada') {
                    // Verificar si la cantidad no supera el máximo permitido (30 en este caso)
                    $nuevaCantidad = $stock->cantidad + $detalle->ajus_det_cantidad;
                    if ($nuevaCantidad > 30) {
                        return response()->json([
                            'mensaje' => 'La cantidad en stock no puede superar el límite máximo de 30.',
                            'tipo' => 'error'
                        ], 400);
                    }
                    // Si es entrada y no supera el máximo, actualizamos el stock
                    $stock->cantidad = $nuevaCantidad;
                    $stock->save();
                } elseif ($ajustecab->tipo_ajuste === 'Salida') {
                    // Si es salida, verificamos si hay suficiente stock para la salida (no podemos tener menos de 0)
                    if ($stock->cantidad + $detalle->ajus_det_cantidad < 0) {
                        return response()->json([
                            'mensaje' => 'No hay suficiente stock para realizar la salida del item: ' . $detalle->item_id,
                            'tipo' => 'error'
                        ], 400);
                    }
                    // Si hay suficiente stock, restamos la cantidad
                    $stock->cantidad += $detalle->ajus_det_cantidad; // Ajus_det_cantidad es negativo en este caso
                    $stock->save();
                }
            } else {
                return response()->json([
                    'mensaje' => 'El item ' . $detalle->item_id . ' no tiene stock registrado.',
                    'tipo' => 'error'
                ], 400);
            }
        }
    
        return response()->json([
            'mensaje' => 'Ajuste confirmado con éxito y stock actualizado.',
            'tipo' => 'success',
            'registro' => $ajustecab
        ], 200);
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
