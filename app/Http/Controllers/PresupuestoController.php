<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Presupuesto;
use App\Models\PresupuestosDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresupuestoController extends Controller
{
    public function read(){
        return DB::select("select 
        p.*,
        to_char(p.pre_vence, ' dd/mm/yyy HH24:mi:ss ') as pre_vence,
        p2.prov_razonsocial,
        p2.prov_ruc,
        p2.prov_telefono,
        p2.prov_correo,
        'PEDIDO NRO: '||to_char(p.pedido_id , '0000000')||' VENCE EL:'||to_char(p3.ped_vence, 'dd/mm/yyy HH24:mi:ss')||' ('||p3.ped_pbservaciones||')' as pedido,
        to_char(p.pedido_id , '0000000') as nro_pedido,
        u.name,
        u.login 
        from presupuestos p 
        join proveedores p2 on p2.id = p.proveedor_id 
        join pedidos p3 on p3.id = p.pedido_id 
        join users u on u.id = p.user_id;");
    }

    public function store(Request $r){
        $datosValidados = $r->validate([
            'pre_observaciones'=>'required',
            'pre_estado'=>'required',
            'pre_vence'=>'required',
            'proveedor_id'=>'required',
            'pedido_id'=>'required',
            'user_id'=>'required'
        ]);
        $presupuesto = Presupuesto::create($datosValidados);
        $presupuesto->save();


        $pedido = Pedido::find($r->pedido_id);
        $pedido->ped_estado ="PROCESADO";
        $pedido ->save();

        $detalles = DB::select("select 
        pd.*,
        i.item_costo 
        from pedidos_detalles pd
        join items i ON i.id = pd.item_id 
        where pedidos_id = $r->pedido_id;");

        foreach ($detalles as $dp){
           $presupuestosDetalle = new PresupuestosDetalle();
           $presupuestosDetalle->presupuesto_id = $presupuesto->id;
           $presupuestosDetalle->item_id = $dp->item_id;
           $presupuestosDetalle->det_costo = $dp->item_costo;
           $presupuestosDetalle->det_cantidad = $dp->det_cantidad;
           $presupuestosDetalle->save();
        }

        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $presupuesto
        ],200);
    }

    public function update(Request $r, $id){
        $presupuesto = Presupuesto::find($id);
        if(!$presupuesto){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'pre_observaciones'=>'required',
            'pre_estado'=>'required',
            'pre_vence'=>'required',
            'proveedor_id'=>'required',
            'user_id'=>'required'
        ]);
        $presupuesto->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $presupuesto
        ],200);
    }
    public function destroy($id){
        $presupuesto = Presupuesto::find($id);
        if(!$presupuesto){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $presupuesto->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }

    public function anular(Request $r, $id){
        $presupuesto = Presupuesto::find($id);
        if(!$presupuesto){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'pre_observaciones'=>'required',
            'pre_estado'=>'required',
            'pre_vence'=>'required',
            'proveedor_id'=>'required',
            'user_id'=>'required'
        ]);
        $presupuesto->update($datosValidados);

        $pedido = Pedido::find($r->pedido_id);
        $pedido->ped_estado ="CONFIRMADO";
        $pedido ->save();

        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $presupuesto
        ],200);
    }
    public function confirmar(Request $r, $id){
        $presupuesto = Presupuesto::find($id);
        if(!$presupuesto){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'pre_observaciones'=>'required',
            'pre_estado'=>'required',
            'pre_vence'=>'required',
            'proveedor_id'=>'required',
            'user_id'=>'required'
        ]);
        $presupuesto->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $presupuesto
        ],200);
    }

    public function rechazar(Request $r, $id){
        $presupuesto = Presupuesto::find($id);
        if(!$presupuesto){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'pre_observaciones'=>'required',
            'pre_estado'=>'required',
            'pre_vence'=>'required',
            'proveedor_id'=>'required',
            'user_id'=>'required'
        ]);
        $presupuesto->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro Rechazado con exito',
            'tipo'=>'success',
            'registro'=> $presupuesto
        ],200);
    }

    public function aprobar(Request $r, $id){
        $presupuesto = Presupuesto::find($id);
        if(!$presupuesto){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'pre_observaciones'=>'required',
            'pre_estado'=>'required',
            'pre_vence'=>'required',
            'proveedor_id'=>'required',
            'user_id'=>'required'
        ]);
        $presupuesto->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro Aprobado con exito',
            'tipo'=>'success',
            'registro'=> $presupuesto
        ],200);
    }
}
