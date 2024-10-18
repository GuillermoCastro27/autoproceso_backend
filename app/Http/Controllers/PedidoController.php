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
        TO_CHAR(p.ped_fecha, 'dd/mm/yyyy HH24:mi:ss') AS ped_fecha,
        to_char(p.ped_vence, 'dd/mm/yyy HH24:mi:ss') as ped_vence ,
        p.ped_pbservaciones ,
        p.ped_estado ,
        p.sucursal_id,
        s.suc_razon_social AS suc_razon_social,
        p.empresa_id,
        e.emp_razon_social AS emp_razon_social,
        p.user_id ,
        p.created_at ,
        p.updated_at ,
        u.name, 
        u.login  
        from pedidos p 
        JOIN 
        sucursal s ON s.empresa_id = p.sucursal_id
        JOIN 
        empresa e ON e.id = p.empresa_id 
        join users u on u.id = p.user_id;");
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'ped_vence'=>'required',
            'ped_fecha'=>'required',
            'ped_pbservaciones'=>'required',
            'ped_estado'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
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
            'ped_fecha'=>'required',
            'ped_pbservaciones'=>'required',
            'ped_estado'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $pedido->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $pedido
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
            'ped_fecha'=>'required',
            'ped_pbservaciones'=>'required',
            'ped_estado'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
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
            'ped_fecha'=>'required',
            'ped_pbservaciones'=>'required',
            'ped_estado'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
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
        return DB::select("SELECT 
    p.id,
    to_char(p.ped_vence, 'dd/mm/yyyy HH24:mi:ss') AS ped_vence,
    p.ped_pbservaciones,
    p.ped_estado,
    p.user_id,
    p.created_at,
    p.updated_at,
    u.name,
    u.login,  
    p.id AS pedido_id,
    'PEDIDO NRO: ' || TO_CHAR(p.id, '0000000') || ' VENCE EL: ' || TO_CHAR(p.ped_vence, 'dd/mm/yyyy HH24:mi:ss') || ' (' || p.ped_pbservaciones || ')' AS pedido,
    to_char(p.ped_vence, 'dd/mm/yyyy HH24:mi:ss') as ped_vence,
    p.sucursal_id,
    s.suc_razon_social AS suc_razon_social,
    p.empresa_id,
    e.emp_razon_social AS emp_razon_social
FROM 
    pedidos p
JOIN 
    users u ON u.id = p.user_id
JOIN 
    sucursal s ON s.empresa_id = p.sucursal_id
JOIN 
    empresa e ON e.id = p.empresa_id
WHERE 
    p.ped_estado = 'CONFIRMADO'
    and p.user_id = {$r->user_id} and u.name ilike'%{$r->name}%'");
    }
}
