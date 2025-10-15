<?php

namespace App\Http\Controllers;
use App\Models\SolicitudCab;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class SolicitudCabController extends Controller
{
    public function read(){
        return DB::select("SELECT 
                sc.id,
                TO_CHAR(sc.soli_cab_fecha, 'dd/mm/yyyy HH24:mi:ss') AS soli_cab_fecha,
                TO_CHAR(sc.soli_cab_fecha_estimada, 'dd/mm/yyyy HH24:mi:ss') AS soli_cab_fecha_estimada,
                sc.soli_cab_observaciones,
                sc.soli_cab_prioridad,
                sc.soli_cab_estado,

                sc.sucursal_id,
                s.suc_razon_social AS suc_razon_social,

                sc.empresa_id,
                e.emp_razon_social AS emp_razon_social,

                sc.clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                c.cli_correo,

                sc.tipo_servicio_id,
                ts.tipo_serv_nombre,

                sc.user_id,
                u.name,
                u.login,

                sc.created_at,
                sc.updated_at
            FROM solicitudes_cab sc
            JOIN sucursal s ON s.empresa_id = sc.sucursal_id
            JOIN empresa e ON e.id = sc.empresa_id
            JOIN clientes c ON c.id = sc.clientes_id
            JOIN tipo_servicio ts ON ts.id = sc.tipo_servicio_id
            JOIN users u ON u.id = sc.user_id
            ORDER BY sc.id DESC");
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'soli_cab_observaciones'=>'required',
            'soli_cab_fecha'=>'required',
            'soli_cab_fecha_estimada'=>'required',
            'soli_cab_prioridad'=>'required',
            'soli_cab_estado'=>'required',
            'clientes_id'=>'required',
            'tipo_servicio_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $solicitudcab = SolicitudCab::create($datosValidados);
        $solicitudcab->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $solicitudcab
        ],200);
    }
    public function update(Request $r, $id){
        $solicitudcab = SolicitudCab::find($id);
        if(!$solicitudcab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'soli_cab_observaciones'=>'required',
            'soli_cab_fecha'=>'required',
            'soli_cab_fecha_estimada'=>'required',
            'soli_cab_prioridad'=>'required',
            'soli_cab_estado'=>'required',
            'clientes_id'=>'required',
            'tipo_servicio_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $solicitudcab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $solicitudcab
        ],200);
    }
    public function anular(Request $r, $id){
        $solicitudcab = SolicitudCab::find($id);
        if(!$solicitudcab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'soli_cab_observaciones'=>'required',
            'soli_cab_fecha'=>'required',
            'soli_cab_fecha_estimada'=>'required',
            'soli_cab_prioridad'=>'required',
            'soli_cab_estado'=>'required',
            'clientes_id'=>'required',
            'tipo_servicio_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $solicitudcab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $solicitudcab
        ],200);
    }
    public function confirmar(Request $r, $id){
        $solicitudcab = SolicitudCab::find($id);
        if(!$solicitudcab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'soli_cab_observaciones'=>'required',
            'soli_cab_fecha'=>'required',
            'soli_cab_fecha_estimada'=>'required',
            'soli_cab_prioridad'=>'required',
            'soli_cab_estado'=>'required',
            'clientes_id'=>'required',
            'tipo_servicio_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $solicitudcab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $solicitudcab
        ],200);
    }
    public function buscar(Request $r){
        return DB::select("SELECT 
            sc.id AS solicitudes_cab_id,
            to_char(sc.soli_cab_fecha_estimada, 'dd/mm/yyyy HH24:mi:ss') AS soli_cab_fecha_estimada,
            sc.soli_cab_observaciones,
            sc.soli_cab_estado,
            sc.soli_cab_prioridad,
            sc.user_id,
            u.name AS encargado,
            u.login,
            sc.created_at,
            sc.updated_at,

            -- Cliente
            c.id AS clientes_id,
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- Tipo de servicio
            ts.id AS tipo_servicio_id,
            ts.tipo_serv_nombre AS tipo_servicio,

            -- Empresa y sucursal
            sc.sucursal_id,
            s.suc_razon_social,
            sc.empresa_id,
            e.emp_razon_social,

            -- Texto descriptivo de la solicitud
            'SOLICITUD NRO: ' || TO_CHAR(sc.id, '0000000') || 
            ' (' || sc.soli_cab_observaciones || ')' AS solicitud

        FROM 
            solicitudes_cab sc
        JOIN users u ON u.id = sc.user_id
        JOIN clientes c ON c.id = sc.clientes_id
        JOIN tipo_servicio ts ON ts.id = sc.tipo_servicio_id
        JOIN sucursal s ON s.empresa_id = sc.sucursal_id
        JOIN empresa e ON e.id = sc.empresa_id
        WHERE 
            sc.soli_cab_estado = 'CONFIRMADO'
    and sc.user_id = {$r->user_id} and u.name ilike'%{$r->name}%'");
    }
    public function buscarInforme(Request $request)
{
    $desde = $request->query('desde');
    $hasta = $request->query('hasta');

    return DB::select("
        SELECT 
            p.id,
            to_char(p.ped_fecha, 'dd/mm/yyyy') AS fecha,
            to_char(p.ped_vence, 'dd/mm/yyyy') AS entrega,
            p.ped_pbservaciones AS observaciones,
            p.ped_estado AS estado,
            u.name AS encargado,
            s.suc_razon_social AS sucursal,
            e.emp_razon_social AS empresa
        FROM pedidos p
        JOIN users u ON u.id = p.user_id
        JOIN sucursal s ON s.empresa_id = p.sucursal_id
        JOIN empresa e ON e.id = p.empresa_id
        WHERE p.ped_estado = 'PROCESADO'
            AND p.ped_fecha BETWEEN ? AND ?
        ORDER BY p.ped_fecha ASC
    ", [$desde, $hasta]);
}
}
