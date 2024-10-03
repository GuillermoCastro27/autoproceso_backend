<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function read(){
        return DB::select("select 
        p.id ,
        to_char(p.ped_vence, 'dd/mm/yyy HH24:mi:ss') as ped_vence ,
        p.ped_pbservaciones ,
        p.ped_estado ,
        p.user_id ,
        p.created_at ,
        p.updated_at ,
        u.name, 
        u.login  
        from pedidos p join users u on u.id = p.user_id;");
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'ped_vence'=>'required',
            'ped_pbservaciones'=>'required',
            'ped_estado'=>'required',
            'user_id'=>'required'
        ]);
        $pedido = Pedido::create($datosValidados);
        $pedido->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $pedido
        ],200);
    }
    public function update(Request $r, $id){
        $pedido = Pedido::find($id);
        if(!$pedido){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'ped_vence'=>'required',
            'ped_pbservaciones'=>'required',
            'ped_estado'=>'required',
            'user_id'=>'required'
        ]);
        $pedido->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $pedido
        ],200);
    }
    public function destroy($id){
        $pedido = Pedido::find($id);
        if(!$pedido){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $pedido->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }

    public function anular(Request $r, $id){
        $pedido = Pedido::find($id);
        if(!$pedido){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'ped_vence'=>'required',
            'ped_pbservaciones'=>'required',
            'ped_estado'=>'required',
            'user_id'=>'required'
        ]);
        $pedido->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $pedido
        ],200);
    }
    public function confirmar(Request $r, $id){
        $pedido = Pedido::find($id);
        if(!$pedido){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'ped_vence'=>'required',
            'ped_pbservaciones'=>'required',
            'ped_estado'=>'required',
            'user_id'=>'required'
        ]);
        $pedido->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $pedido
        ],200);
    }
    public function eliminar($id){
        $pedido = Pedido::find($id);
        if(!$pedido){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $pedido->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }


    public function buscar(Request $r){
        return DB::select("select 
        p.id ,
        to_char(p.ped_vence, 'dd/mm/yyy HH24:mi:ss') as ped_vence ,
        p.ped_pbservaciones ,
        p.ped_estado ,
        p.user_id ,
        p.created_at ,
        p.updated_at ,
        u.name, 
        u.login,  
        p.id as pedido_id,
        'PEDIDO NRO: '||to_char(p.id , '0000000')||' VENCE EL:'||to_char(p.ped_vence, 'dd/mm/yyy HH24:mi:ss')||' ('||ped_pbservaciones||')' as pedido  
        from pedidos p join users u on u.id = p.user_id 
        where ped_estado ='CONFIRMADO' and p.user_id = {$r->user_id} and u.name ilike'%{$r->name}%'");
    }
}
