<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PresupuestosDetalle;
use Illuminate\Support\Facades\DB;

class PresupuestosDetalleController extends Controller
{
    public function read($id){
        return DB::select("select 
        pd.*, 
        i.item_decripcion
        from presupuestos_detalles pd 
        join items i on i.id = pd.item_id 
        where pd.presupuesto_id = $id;");
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            "presupuesto_id"=>"required",
            "item_id"=>"required",
            "det_cantidad"=>"required",
            "det_costo"=>"required"
        ]);
        $detalle = PresupuestosDetalle::create($datosValidados);
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $detalle
        ],200);
    }
    public function update(Request $r, $presupuesto_id, $item_id)
    {
        // Normalizamos el costo: quitamos puntos de miles y cambiamos la coma por punto
        $costo = str_replace('.', '', $r->det_costo);  // elimina separador de miles
        $costo = str_replace(',', '.', $costo);       // cambia coma decimal a punto
        $costo = floatval($costo);                    // lo convierte a número válido

        // Actualizar el registro
        DB::table('presupuestos_detalles')
            ->where('presupuesto_id', $presupuesto_id)
            ->where('item_id', $item_id)
            ->update([
                'det_costo' => $costo
            ]);

        // Traer el registro actualizado
        $detalle = DB::select("
            SELECT * 
            FROM presupuestos_detalles 
            WHERE presupuesto_id = ? AND item_id = ?
        ", [$presupuesto_id, $item_id]);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo'    => 'success',
            'registro'=> $detalle
        ], 200);
    }
    public function destroy($presupuesto_id, $item_id){
        $detalle = DB::table('presupuestos_detalles')->
        where('presupuesto_id', $presupuesto_id)->
        where('item_id', $item_id)->
        delete();

        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
            'registro'=> $detalle
        ],200);
    }
}
