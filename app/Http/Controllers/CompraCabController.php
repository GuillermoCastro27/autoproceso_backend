<?php

namespace App\Http\Controllers;

use App\Models\CompraCab;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompraCabController extends Controller
{
    public function read() {
        return DB::select("
            SELECT 
                c.*,
                to_char(c.comp_intervalo_fecha_vence, 'dd/mm/yyyy HH24:mi:ss') as comp_intervalo_fecha_vence,
                to_char(c.comp_fecha, 'dd/mm/yyyy') as comp_fecha,
                p.prov_razonsocial,
                p.prov_ruc,
                p.prov_telefono,
                p.prov_correo,
                e.emp_razon_social,
                s.suc_razon_social,
                u.name as encargado,
                'ORDEN DE COMPRA NRO: ' || to_char(oc.id, '0000000') || ' VENCE EL: ' || to_char(c.comp_intervalo_fecha_vence, 'dd/mm/yyyy HH24:mi:ss') as ordencompra
            FROM compra_cab c
            JOIN proveedores p ON p.id = c.proveedor_id
            JOIN empresa e ON e.id = c.empresa_id
            JOIN sucursal s ON s.empresa_id = c.sucursal_id
            JOIN users u ON u.id = c.user_id
            JOIN orden_compra_cab oc ON oc.id = c.orden_compra_cab_id;
        ");
    }    
    public function store(Request $r){
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence'=>'required',
            'comp_fecha'=>'required',
            'comp_estado'=>'required',
            'comp_cant_cuota'=>'required',
            'condicion_pago'=>'required',
            'user_id'=>'required',
            'orden_compra_cab_id'=>'required',
            'proveedor_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $compracab = CompraCab::create($datosValidados);
        $compracab->save();

        $ordencompracab = OrdenCompraCab::find($r->orden_compra_cab_id);
        $ordencompracab->ord_comp_estado ="PROCESADO";
        $ordencompracab ->save();

        $detalles = DB::select("select 
        odc.*,
        i.item_costo 
        from orden_compra_det odc
        join items i ON i.id = odc.item_id 
        where orden_compra_cab_id = $r->orden_compra_cab_id;");

        foreach ($detalles as $odc){
           $compraDetalle = new CompraDetalle();
           $compraDetalle->presupuesto_id = $presupuesto->id;
           $compraDetalle->item_id = $dp->item_id;
           $compraDetalle->det_costo = $dp->item_costo;
           $compraDetalle->det_cantidad = $dp->det_cantidad;
           $compra8Detalle->save();
        }

        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $ordencompracab
        ],200);
    }

    public function update(Request $r, $id){
        $ordencompracab = CompraCab::find($id);
        if(!$ordencompracab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence'=>'required',
            'comp_fecha'=>'required',
            'comp_estado'=>'required',
            'comp_cant_cuota'=>'required',
            'condicion_pago'=>'required',
            'user_id'=>'required',
            'orden_compra_cab_id'=>'required',
            'proveedor_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $ordencompracab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $ordencompracab
        ],200);
    }
    public function destroy($id){
        $ordencom8pracab = CompraCab::find($id);
        if(!$ordencompracab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $ordencompracab->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }

    public function anular(Request $r, $id){
        $ordencompracab = CompraCab::find($id);
        if(!$ordencompracab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence'=>'required',
            'comp_fecha'=>'required',
            'comp_estado'=>'required',
            'comp_cant_cuota'=>'required',
            'condicion_pago'=>'required',
            'user_id'=>'required',
            'orden_compra_cab_id'=>'required',
            'proveedor_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $ordencompracab->update($datosValidados);

        $ordencompracab = OrdenCompraCab::find($r->orden_compra_cab_id);
        $ordencompracab->ord_comp_estado ="CONFIRMADO";
        $ordenco8mpracab ->save();

        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $ordencompracab
        ],200);
    }
    public function confirmar(Request $r, $id){
        $ordencompracab = CompraCab::find($id);
        if(!$ordencompracab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence'=>'required',
            'comp_fecha'=>'required',
            'comp_estado'=>'required',
            'comp_cant_cuota'=>'required',
            'condicion_pago'=>'required',
            'user_id'=>'required',
            'orden_compra_cab_id'=>'required',
            'proveedor_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $ordencompracab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $ordencompracab
        ],200);
    }

    public function rechazar(Request $r, $id){
        $ordencompracab = CompraCab::find($id);
        if(!$or8dencompracab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence'=>'required',
            'comp_fecha'=>'required',
            'comp_estado'=>'required',
            'comp_cant_cuota'=>'required',
            'condicion_pago'=>'required',
            'user_id'=>'required',
            'orden_compra_cab_id'=>'required',
            'proveedor_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $ordencompracab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro Rechazado con exito',
            'tipo'=>'success',
            'registro'=> $ordencompracab
        ],200);
    }

    public function aprobar(Request $r, $id){
        $ordencompracab = CompraCab::find($id);
        if(!$ordencompracab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence'=>'required',
            'comp_fecha'=>'required',
            'comp_estado'=>'required',
            'comp_cant_cuota'=>'required',
            'condicion_pago'=>'required',
            'user_id'=>'required',
            'orden_compra_cab_id'=>'required',
            'proveedor_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $ordencompracab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro Aprobado con exito',
            'tipo'=>'success',
            'registro'=> $ordencompracab
        ],200);
    }
}