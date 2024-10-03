<?php

namespace App\Http\Controllers;

use App\Models\PedidosDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidosDetalleController extends Controller
{
    public function read($id){
        return DB::select("select 
        pd.*, 
        i.item_decripcion
        from pedidos_detalles pd 
        join items i on i.id = pd.item_id 
        where pd.pedidos_id = $id ");
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            "pedidos_id"=>"required",
            "item_id"=>"required",
            "det_cantidad"=>"required"
        ]);
        $detalle = PedidosDetalle::create($datosValidados);
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $detalle
        ],200);
    }

    public function update(Request $r, $pedidos_id, $item_id){
        $detalle = DB::table('pedidos_detalles')->
        where('pedidos_id', $pedidos_id)->
        where('item_id', $item_id)->
        update(['det_cantidad'=>$r->det_cantidad
        ]);
        $detalle = DB::select("select * from pedidos_detalles where pedidos_id = $pedidos_id and item_id = $item_id");
        return response()->json([
            'mensaje'=>'Registro Modificado con exito',
            'tipo'=>'success',
            'registro'=> $detalle
        ],200);
    }

    public function destroy($pedidos_id, $item_id){
        $detalle = DB::table('pedidos_detalles')->
        where('pedidos_id', $pedidos_id)->
        where('item_id', $item_id)->
        delete();

        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
            'registro'=> $detalle
        ],200);
    }
}
