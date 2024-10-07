<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompraCab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenCompraCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
            o.id,
            to_char(o.ord_comp_intervalo_fecha_vence, 'dd/mm/yyyy HH24:mi:ss') AS ord_comp_intervalo_fecha_vence,
            o.ord_comp_fecha,
            o.ord_comp_estado,
            o.ord_comp_cant_cuota,
            p.id AS proveedor_id,  -- Cambiado para asegurarse de que se obtenga del proveedor
            p.prov_razonsocial AS prov_razonsocial,
            p.prov_ruc AS prov_ruc,
            p.prov_telefono AS prov_telefono,
            p.prov_correo AS prov_correo,
            o.sucursal_id,
            s.suc_razon_social AS suc_razon_social,
            o.empresa_id,
            e.emp_razon_social AS emp_razon_social,
            pr.id AS presupuesto_id,  -- ID del presupuesto
            'PRESUPUESTO NRO: ' || to_char(pr.id, '0000000') || ' VENCE EL: ' || to_char(pr.pre_vence, 'dd/mm/yyyy HH24:mi:ss') || ' (' || pr.pre_observaciones || ')' AS presupuesto,
            u.name AS encargado  
        FROM 
            orden_compra_cab o
        JOIN 
            users u ON u.id = o.user_id
        JOIN 
            sucursal s ON s.empresa_id = o.sucursal_id
        JOIN 
            empresa e ON e.id = o.empresa_id
        JOIN 
            presupuestos pr ON pr.id = o.presupuesto_id
        JOIN 
            proveedores p ON p.id = pr.proveedor_id
    ");
}
public function store(Request $r){
    $datosValidados = $r->validate([
        'ord_comp_intervalo_fecha_vence'=>'required',
        'ord_comp_fecha'=>'required',
        'ord_comp_estado'=>'required',
        'ord_comp_cant_cuota'=>'required',
        'user_id'=>'required',
        'presupuesto_id'=>'required',
        'proveedor_id'=>'required',
        'empresa_id'=>'required',
        'sucursal_id'=>'required'
    ]);
    $ordencompracab = OrdenCompraCab::create($datosValidados);
    $ordencompracab->save();
    return response()->json([
        'mensaje'=>'Registro creado con exito',
        'tipo'=>'success',
        'registro'=> $ordencompracab
    ],200);
}
public function update(Request $r, $id){
    $ordencompracab = OrdenCompraCab::find($id);
    if(!$ordencompracab){
        return response()->json([
            'mensaje'=>'Registro no encontrado',
            'tipo'=>'error'
        ],404);
    }
    $datosValidados = $r->validate([
        'ord_comp_intervalo_fecha_vence'=>'required',
        'ord_comp_fecha'=>'required',
        'ord_comp_estado'=>'required',
        'ord_comp_cant_cuota'=>'required',
        'user_id'=>'required',
        'presupuesto_id'=>'required',
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
public function eliminar($id){
    $ordencompracab = OrdenCompraCab::find($id);
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
    $ordencompracab = OrdenCompraCab::find($id);
    if(!$ordencompracab){
        return response()->json([
            'mensaje'=>'Registro no encontrado',
            'tipo'=>'error'
        ],404);
    }
    $datosValidados = $r->validate([
        'ord_comp_intervalo_fecha_vence'=>'required',
        'ord_comp_fecha'=>'required',
        'ord_comp_estado'=>'required',
        'ord_comp_cant_cuota'=>'required',
        'user_id'=>'required',
        'presupuesto_id'=>'required',
        'proveedor_id'=>'required',
        'empresa_id'=>'required',
        'sucursal_id'=>'required'
    ]);
    $ordencompracab->update($datosValidados);
    return response()->json([
        'mensaje'=>'Registro anulado con exito',
        'tipo'=>'success',
        'registro'=> $ordencompracab
    ],200);
}
public function confirmar(Request $r, $id) {
    $ordencompracab = OrdenCompraCab::find($id);
    if (!$ordencompracab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }
    
    // Validar que los IDs no sean 0 o nulos
    $proveedor_id = $r->proveedor_id;
    if ($proveedor_id == 0 || is_null($proveedor_id)) {
        return response()->json([
            'mensaje' => 'El proveedor ID es inválido',
            'tipo' => 'error'
        ], 422);
    }

    // Validar otros campos
    $datosValidados = $r->validate([
        'ord_comp_intervalo_fecha_vence' => 'required',
        'ord_comp_fecha' => 'required',
        'ord_comp_estado' => 'required',
        'ord_comp_cant_cuota' => 'required',
        'user_id' => 'required',
        'presupuesto_id' => 'required',
        'proveedor_id' => 'required',
        'empresa_id' => 'required',
        'sucursal_id' => 'required'
    ]);
    
    $ordencompracab->update($datosValidados);
    return response()->json([
        'mensaje' => 'Registro confirmado con éxito',
        'tipo' => 'success',
        'registro' => $ordencompracab
    ], 200);
}
}
