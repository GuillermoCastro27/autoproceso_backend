<?php

namespace App\Http\Controllers;

use App\Models\DescuentosCab;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DescuentosCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
            dc.id,
            dc.desc_cab_nombre ,
            dc.desc_cab_observaciones,
            TO_CHAR(dc.desc_cab_fecha_registro, 'DD/MM/YYYY HH24:MI:SS') AS desc_cab_fecha_registro,
            TO_CHAR(dc.desc_cab_fecha_inicio, 'DD/MM/YYYY HH24:MI:SS') AS desc_cab_fecha_inicio,
            TO_CHAR(dc.desc_cab_fecha_fin, 'DD/MM/YYYY HH24:MI:SS') AS desc_cab_fecha_fin,
            dc.desc_cab_estado,
            dc.desc_cab_porcentaje,
            dc.sucursal_id,
            s.suc_razon_social,
            dc.empresa_id,
            e.emp_razon_social,
            dc.user_id,
            u.name,
            u.login,
            dc.tipo_descuentos_id,
            td.tipo_desc_nombre AS tipo_desc_nombre,
            dc.created_at,
            dc.updated_at
        FROM descuentos_cab dc 
        JOIN sucursal s ON s.empresa_id = dc.sucursal_id
        JOIN empresa e  ON e.id = dc.empresa_id
        JOIN users u    ON u.id = dc.user_id
        JOIN tipo_descuentos td ON td.id = dc.tipo_descuentos_id
        ORDER BY dc.id desc;
    ");
}
public function store(Request $r){
        $datosValidados = $r->validate([
            'desc_cab_nombre'=>'required',
            'desc_cab_observaciones'=>'required',
            'desc_cab_fecha_registro'=>'required',
            'desc_cab_fecha_inicio'=>'required',
            'desc_cab_fecha_fin'=>'required',
            'desc_cab_estado'=>'required',
            'desc_cab_porcentaje'=>'required',
            'tipo_descuentos_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $descuentoscab = DescuentosCab::create($datosValidados);
        $descuentoscab->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $descuentoscab
        ],200);
    }
    public function update(Request $r, $id){
        $descuentoscab = DescuentosCab::find($id);
        if(!$descuentoscab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'desc_cab_nombre'=>'required',
            'desc_cab_observaciones'=>'required',
            'desc_cab_fecha_registro'=>'required',
            'desc_cab_fecha_inicio'=>'required',
            'desc_cab_fecha_fin'=>'required',
            'desc_cab_estado'=>'required',
            'desc_cab_porcentaje'=>'required',
            'tipo_descuentos_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $descuentoscab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $descuentoscab
        ],200);
    }
    public function anular(Request $r, $id){
        $descuentoscab = DescuentosCab::find($id);
        if(!$descuentoscab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'desc_cab_nombre'=>'required',
            'desc_cab_observaciones'=>'required',
            'desc_cab_fecha_registro'=>'required',
            'desc_cab_fecha_inicio'=>'required',
            'desc_cab_fecha_fin'=>'required',
            'desc_cab_estado'=>'required',
            'desc_cab_porcentaje'=>'required',
            'tipo_descuentos_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $descuentoscab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $descuentoscab
        ],200);
    }
    public function confirmar(Request $r, $id){
        $descuentoscab = DescuentosCab::find($id);
        if(!$descuentoscab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'desc_cab_nombre'=>'required',
            'desc_cab_observaciones'=>'required',
            'desc_cab_fecha_registro'=>'required',
            'desc_cab_fecha_inicio'=>'required',
            'desc_cab_fecha_fin'=>'required',
            'desc_cab_estado'=>'required',
            'desc_cab_porcentaje'=>'required',
            'tipo_descuentos_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $descuentoscab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $descuentoscab
        ],200);
    }
    public function buscar(Request $r)
{
    $texto = $r->input('texto');
    $userId = $r->input('user_id');

    // 🔹 Primero: anula automáticamente los descuentos vencidos
    DB::update("
        UPDATE descuentos_cab
        SET desc_cab_estado = 'ANULADO'
        WHERE desc_cab_estado = 'CONFIRMADO'
        AND desc_cab_fecha_fin < CURRENT_TIMESTAMP
    ");

    // 🔹 Luego: busca solo los descuentos vigentes
    return DB::select("
        SELECT 
            dc.id AS descuentos_cab_id,
            dc.desc_cab_nombre,
            dc.desc_cab_observaciones,

            TO_CHAR(dc.desc_cab_fecha_registro, 'DD/MM/YYYY HH24:MI:SS') AS desc_cab_fecha_registro,
            TO_CHAR(dc.desc_cab_fecha_inicio, 'DD/MM/YYYY HH24:MI:SS') AS desc_cab_fecha_inicio,
            TO_CHAR(dc.desc_cab_fecha_fin, 'DD/MM/YYYY HH24:MI:SS') AS desc_cab_fecha_fin,

            dc.desc_cab_estado,
            dc.desc_cab_porcentaje,
            dc.tipo_descuentos_id,
            td.tipo_desc_nombre AS tipo_desc_nombre,

            -- Usuario
            u.id AS user_id,
            u.name AS encargado,
            u.login,

            -- Empresa y Sucursal
            dc.empresa_id,
            e.emp_razon_social,
            dc.sucursal_id,
            s.suc_razon_social,

            -- Texto descriptivo
            'DESCUENTO NRO: ' || TO_CHAR(dc.id, '0000000') || 
            ' (' || dc.desc_cab_nombre || ')' AS desc_cab_nombre

        FROM descuentos_cab dc
        JOIN users u ON u.id = dc.user_id
        JOIN empresa e ON e.id = dc.empresa_id
        JOIN sucursal s ON s.empresa_id = dc.sucursal_id
        JOIN tipo_descuentos td ON td.id = dc.tipo_descuentos_id
        WHERE 
            dc.desc_cab_estado = 'CONFIRMADO'
            AND u.id = {$userId}
            AND CURRENT_TIMESTAMP BETWEEN dc.desc_cab_fecha_inicio AND dc.desc_cab_fecha_fin
            AND (
                dc.desc_cab_nombre ILIKE '%{$texto}%'
                OR dc.desc_cab_observaciones ILIKE '%{$texto}%'
                OR td.tipo_desc_nombre ILIKE '%{$texto}%'
                OR TO_CHAR(dc.id, '0000000') ILIKE '%{$texto}%'
            )
        ORDER BY dc.id DESC
    ");
}

}
