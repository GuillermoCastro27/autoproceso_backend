<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function read(){
        return DB::table('items')
        ->join('marca', 'items.marca_id', '=', 'marca.id')
        ->join('modelo', 'items.modelo_id', '=', 'modelo.id')
        ->join('tipos', 'items.tipo_id', '=', 'tipos.id')
        ->join('tipo_impuesto', 'items.tipo_impuesto_id', '=', 'tipo_impuesto.id')
        ->select('items.*','marca.marc_nom as marc_nom','modelo.modelo_nom as modelo_nom','tipos.tipo_descripcion as tipo_descripcion','tipo_impuesto.tip_imp_nom as tip_imp_nom')
        ->get();
    }

    public function store(Request $r){
        $datosValidados = $r->validate([
            'item_decripcion'=>'required',
            'item_costo'=>'required',
            'item_precio'=>'required',
            'tipo_id'=>'required',
            'tipo_impuesto_id'=>'required',
            'marca_id'=>'required',
            'modelo_id'=>'required'
        ]);
        $item = Item::create($datosValidados);
        $item->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $item
        ],200);
    }

    public function update(Request $r, $id){
        $item = Item::find($id);
        if(!$item){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'item_decripcion'=>'required',
            'item_costo'=>'required',
            'item_precio'=>'required',
            'tipo_id'=>'required',
            'tipo_impuesto_id'=>'required',
            'marca_id'=>'required',
            'modelo_id'=>'required'
        ]);
        $item->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $item
        ],200);
    }

    public function destroy($id){
        $item = Item::find($id);
        if(!$item){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $item->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }

    public function buscar(Request $r){
        return DB::select("select i.*, t.tipo_descripcion, i.id as item_id
        from items i  join tipos t on t.id = i.tipo_id
        where item_decripcion ilike '%$r->item_decripcion%'
        and tipo_descripcion = '$r->tipo_descripcion'");
    }
}
