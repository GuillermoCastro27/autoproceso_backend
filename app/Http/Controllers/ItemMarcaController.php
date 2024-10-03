<?php

namespace App\Http\Controllers;
use App\Models\ItemMarca;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class ItemMarcaController extends Controller
{
   public function read(){
    return DB::table('item_marca')
         ->join('marca', 'item_marca.marca_id', '=', 'marca.id')
         ->join('items', 'item_marca.item_id', '=', 'items.id')
         ->select('item_marca.*', 'marca.marc_nom', 'items.item_decripcion')
         ->get();
        }
    public function store(Request $r){
        $datosValidados = $r->validate([
            "marca_id"=>"required",
            "item_id"=>"required",
            "item_marca_descrip"=>"required"
        ]);
        $itemmarca = ItemMarca::create($datosValidados);
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $itemmarca
        ],200);
    }
    public function update(Request $r, $marca_id, $item_id){
        $itemmarca = DB::table('item_marca')->
        where('marca_id', $marca_id)->
        where('item_id', $item_id)->
        update(['item_marca_descrip'=>$r->item_marca_descrip
        ]);
        $itemmarca = DB::select("select * from item_marca where marca_id = $marca_id and item_id = $item_id");
        return response()->json([
            'mensaje'=>'Registro Modificado con exito',
            'tipo'=>'success',
            'registro'=> $itemmarca
        ],200);
    }    
    public function destroy($marca_id, $item_id) {
        \Log::info("Intentando eliminar registro con marca_id: $marca_id, item_id: $item_id");
        
        $itemmarca = DB::table('item_marca')
            ->where('marca_id', $marca_id)
            ->where('item_id', $item_id)
            ->delete();
    
        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo' => 'success'
        ], 200);
    }
}
