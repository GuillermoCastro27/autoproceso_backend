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
        p.sucursal_id,
        s.suc_razon_social as suc_razon_social,
        p.empresa_id,
        e.emp_razon_social AS emp_razon_social,
        'PEDIDO NRO: '||to_char(p.pedido_id , '0000000')||' VENCE EL:'||to_char(p3.ped_vence, 'dd/mm/yyy HH24:mi:ss')||' ('||p3.ped_pbservaciones||')' as pedido,
        to_char(p.pedido_id , '0000000') as nro_pedido,
        u.name,
        u.login 
        from presupuestos p 
        join proveedores p2 on p2.id = p.proveedor_id 
        JOIN 
            sucursal s ON s.empresa_id = p.sucursal_id
        JOIN 
            empresa e ON e.id = p.empresa_id
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
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
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
            'pedido_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
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
            'pedido_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
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
            'pedido_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $presupuesto->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $presupuesto
        ],200);
    }
    public function buscar(Request $r) {
        $userId = $r->input('user_id'); // Obtener el valor desde la request
        $userName = $r->input('name');  // Obtener el valor del nombre
    
        return DB::select("
            SELECT 
                p.id AS presupuesto_id,
                TO_CHAR(p.pre_vence, 'dd/mm/yyyy HH24:mi:ss') AS pre_vence,
                p.pre_observaciones,
                p.pre_estado,
                p.user_id,
                p.created_at,
                p.updated_at,
                u.name, 
                u.email,
                p.proveedor_id,
                prov.prov_razonsocial,
                prov.prov_ruc,
                prov.prov_telefono,
                prov.prov_correo,
                'PRESUPUESTO NRO: ' || TO_CHAR(p.id, '0000000') || ' VENCE EL: ' || TO_CHAR(p.pre_vence, 'dd/mm/yyyy HH24:mi:ss') || ' (' || p.pre_observaciones || ')' AS presupuesto
            FROM 
                presupuestos p
            JOIN 
                users u ON u.id = p.user_id
            JOIN 
                proveedores prov ON prov.id = p.proveedor_id
            WHERE 
                p.pre_estado = 'CONFIRMADO'
            AND 
                p.user_id = ?
            AND 
                u.name ILIKE ?
        ", [$userId, '%' . $userName . '%']); // Utilizar bindings seguros
    }
                 
}
