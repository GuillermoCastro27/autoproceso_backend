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
            o.user_id,
            o.created_at,
            o.updated_at,
            u.name,
            u.login,
            p.prov_razonsocial AS prov_razonsocial,
            s.suc_razon_social AS sucursal_razon_social,
            e.emp_razon_social AS emp_razon_social
        FROM 
            orden_compra_cab o
        JOIN 
            users u ON u.id = o.user_id
        JOIN 
            proveedores p ON p.id = o.proveedor_id
        JOIN 
            sucursal s ON s.empresa_id = o.sucursal_id
        JOIN 
            empresa e ON e.id = o.empresa_id;
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
}
