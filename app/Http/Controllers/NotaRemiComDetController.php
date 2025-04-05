<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\NotaRemiComDet;

class NotaRemiComDetController extends Controller
{
    public function read($id){
        return DB::select("select 
        nrcd.*, 
        i.item_decripcion
        from nota_remi_com_det nrcd 
        join items i on i.id = nrcd.item_id 
        where nrcd.nota_remi_comp_id = $id ");
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            "nota_remi_comp_id"=>"required",
            "item_id"=>"required",
            "nota_remi_com_det_cantidad"=>"required"
        ]);
        $detalle = NotaRemiComDet::create($datosValidados);
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $detalle
        ],200);
    }
    public function update(Request $r, $nota_remi_comp_id){
        // Actualizar el registro
        DB::table('nota_remi_com_det')
            ->where('nota_remi_comp_id', $nota_remi_comp_id)
            ->update([
                'item_id' => $r->item_id,
                'nota_remi_com_det_cantidad' => $r->nota_remi_com_det_cantidad
            ]);
    
        // Definir item_id correctamente
        $item_id = $r->item_id;
    
        // Consultar el registro actualizado
        $detalle = DB::select("SELECT * FROM nota_remi_com_det WHERE nota_remi_comp_id = ? AND item_id = ?", [$nota_remi_comp_id, $item_id]);
    
        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => $detalle
        ], 200);
    }
    
    public function destroy($nota_remi_comp_id, $item_id){
        $detalle = DB::table('nota_remi_com_det')->
        where('nota_remi_comp_id', $nota_remi_comp_id)->
        where('item_id', $item_id)->
        delete();

        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
            'registro'=> $detalle
        ],200);
    }
}
