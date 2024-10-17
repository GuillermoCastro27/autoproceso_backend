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
        // Convertir cadena vacía a null antes de la validación
    if ($r->ord_comp_intervalo_fecha_vence === '') {
        $r->merge(['comp_intervalo_fecha_vence' => null]);
    }

    // Establecer ord_comp_cant_cuota como null si la condición de pago es "CONTADO"
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge(['comp_cant_cuota' => null]); // Establece null para cuotas en "CONTADO"
    }
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence'=>'required',
            'comp_fecha'=>'required',
            'comp_estado'=>'required',
            'comp_cant_cuota'=>'nullable|integer',
            'condicion_pago'=>'required',
            'user_id'=>'required',
            'orden_compra_cab_id'=>'required',
            'proveedor_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        if ($r->condicion_pago === 'CONTADO') {
            $datosValidados['comp_intervalo_fecha_vence'] = null; // Establece null si es "CONTADO"
            $datosValidados['comp_cant_cuota'] = null; // Establece null si es "CONTADO"
        }
        $compracab = CompraCab::create($datosValidados);

        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $compracab
        ],200);
    }

    public function update(Request $r, $id){
        $compracab = CompraCab::find($id);
        if(!$compracab){
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
        $compracab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $compracab
        ],200);
    }
    public function destroy($id){
        $compracab = CompraCab::find($id);
        if(!$compracab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $compracab->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }

    public function anular(Request $r, $id){
        $compracab = CompraCab::find($id);
        if(!$compracab){
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
        $compracab->update($datosValidados);

        $ordencompracab = OrdenCompraCab::find($r->orden_compra_cab_id);
        $ordencompracab->ord_comp_estado ="CONFIRMADO";
        $ordenco8mpracab ->save();

        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $compracab
        ],200);
    }
    public function confirmar(Request $r, $id){
        $compracab = CompraCab::find($id);
        if(!$compracab){
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
        $compracab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $compracab
        ],200);
    }

    public function rechazar(Request $r, $id){
        $compracab = CompraCab::find($id);
        if(!$compracab){
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
        $compracab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro Rechazado con exito',
            'tipo'=>'success',
            'registro'=> $compracab
        ],200);
    }

    public function aprobar(Request $r, $id){
        $compracab = CompraCab::find($id);
        if(!$compracab){
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
        $compracab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro Aprobado con exito',
            'tipo'=>'success',
            'registro'=> $compracab
        ],200);
    }
}