<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemModelo;
use Illuminate\Support\Facades\DB;


class ItemModeloController extends Controller
{
    public function read(){
        return DB::table('item_modelo')
             ->join('modelo', 'item_modelo.modelo_id', '=', 'modelo.id')
             ->join('items', 'item_modelo.item_id', '=', 'items.id')
             ->select('item_modelo.*', 'modelo.modelo_nom', 'items.item_decripcion')
             ->get();
            }
    public function store(Request $r){
        $datosValidados = $r->validate([
            "modelo_id"=>"required",
            "item_id"=>"required",
            "item_modelo_descrip"=>"required"
        ]);
        $itemmodelo = ItemModelo::create($datosValidados);
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $itemmodelo
        ],200);
    }
    public function update(Request $r, $modelo_id, $item_id){
        $itemmodelo = DB::table('item_modelo')->
        where('modelo_id', $modelo_id)->
        where('item_id', $item_id)->  
        update(['item_modelo_descrip'=>$r->item_modelo_descrip
        ]);
        $itemmodelo = DB::select("select * from item_modelo where modelo_id = $modelo_id and item_id = $item_id");
        return response()->json([
            'mensaje'=>'Registro Modificado con exito',
            'tipo'=>'success',
            'registro'=> $itemmodelo
        ],200);
    }    
    public function destroy($modelo_id, $item_id){
        $itemmodelo = DB::table('item_modelo')->
        where('modelo_id', $modelo_id)->
        where('item_id', $item_id)->
        delete();

        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
            'registro'=> $itemmodelo
        ],200);
    }
}
