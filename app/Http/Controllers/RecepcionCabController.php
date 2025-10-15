<?php

namespace App\Http\Controllers;
use App\Models\RecepcionCab;
use App\Models\SolicitudCab;
use App\Models\RecepcionDet;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class RecepcionCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
            rc.id,
            rc.recep_cab_fecha,
            rc.recep_cab_fecha_estimada,
            rc.recep_cab_prioridad,
            rc.recep_cab_estado,
            rc.recep_cab_observaciones,
            rc.recep_cab_kilometraje,
            rc.recep_cab_nivel_combustible,

            -- Cliente
            c.id AS clientes_id,
            c.cli_nombre,
            c.cli_apellido,       
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- Sucursal y Empresa (según tu modelo)
            rc.sucursal_id,
            s.suc_razon_social,
            rc.empresa_id,
            e.emp_razon_social,

            -- Solicitud
            sc.id AS solicitudes_cab_id,
            sc.soli_cab_fecha,
            sc.soli_cab_fecha_estimada AS soli_cab_fecha_estimada,
            sc.soli_cab_prioridad AS soli_cab_prioridad,
            sc.soli_cab_observaciones AS soli_cab_observaciones,

            -- Tipo de servicio
            ts.id AS tipo_servicio_id,
            ts.tipo_serv_nombre AS tipo_servicio,

            -- Texto para mostrar solicitud
           'SOLICITUD NRO: ' || to_char(sc.id, '0000000') AS solicitudes,

            -- Encargado
            u.name AS encargado

        FROM recep_cab rc
        JOIN users u ON u.id = rc.user_id
        JOIN sucursal s ON s.empresa_id = rc.sucursal_id  -- 🔹 Mantenido según tu modelo
        JOIN empresa e ON e.id = rc.empresa_id
        JOIN solicitudes_cab sc ON sc.id = rc.solicitudes_cab_id
        JOIN clientes c ON c.id = sc.clientes_id
        LEFT JOIN tipo_servicio ts ON ts.id = rc.tipo_servicio_id
        ORDER BY rc.id DESC
    ");
}
public function store(Request $r){
        $datosValidados = $r->validate([
            'recep_cab_observaciones'=>'required',
            'recep_cab_fecha'=>'required',
            'recep_cab_fecha_estimada'=>'required',
            'recep_cab_prioridad'=>'required',
            'recep_cab_kilometraje'=>'required',
            'recep_cab_nivel_combustible'=>'required',
            'recep_cab_estado'=>'required',
            'solicitudes_cab_id'=>'required',
            'clientes_id'=>'required',
            'tipo_servicio_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $recepcioncab = RecepcionCab::create($datosValidados);
        $recepcioncab->save();

        $solicitudcab = SolicitudCab::find($r->solicitudes_cab_id); // Cambiado a presupuestos_id

        // Verificar si el presupuesto existe
        if (!$solicitudcab) {
            return response()->json([
                'mensaje' => 'Solicitud no encontrada',
                'tipo' => 'error',
            ], 404);
        }
        $solicitudcab->soli_cab_estado = "PROCESADO"; // Cambiado a pre_estado
        $solicitudcab->save();

        // Lógica para guardar detalles
    $detalles = DB::select("SELECT 
    sd.*, 
    i.item_decripcion,
    sd.soli_det_costo as recep_det_costo,
    sd.soli_det_cantidad as recep_det_cantidad,
    sd.soli_det_cantidad_stock as recep_det_cantidad_stock,
    i.tipo_impuesto_id
    FROM solicitudes_det sd 
    JOIN items i ON i.id = sd.item_id 
    WHERE sd.solicitudes_cab_id = $solicitudcab->id;");

    foreach ($detalles as $rd) {
        $recepciondet = new RecepcionDet();
        $recepciondet->recep_cab_id = $recepcioncab->id;
        $recepciondet->item_id = $rd->item_id;
        $recepciondet->recep_det_costo = $rd->recep_det_costo;
        $recepciondet->recep_det_cantidad = $rd->recep_det_cantidad;
        $recepciondet->recep_det_cantidad_stock = $rd->recep_det_cantidad_stock;
        $recepciondet->tipo_impuesto_id = $rd->tipo_impuesto_id; 
        $recepciondet->save();
    }
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $recepcioncab
        ],200);
    }
    public function update(Request $r, $id){
        $recepcioncab = RecepcionCab::find($id);
        if(!$recepcioncab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'recep_cab_observaciones'=>'required',
            'recep_cab_fecha'=>'required',
            'recep_cab_fecha_estimada'=>'required',
            'recep_cab_prioridad'=>'required',
            'recep_cab_kilometraje'=>'required',
            'recep_cab_nivel_combustible'=>'required',
            'recep_cab_estado'=>'required',
            'solicitudes_cab_id'=>'required',
            'clientes_id'=>'required',
            'tipo_servicio_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $recepcioncab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $recepcioncab
        ],200);
    }
    public function anular(Request $r, $id){
        $recepcioncab = RecepcionCab::find($id);
        if(!$recepcioncab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'recep_cab_observaciones'=>'required',
            'recep_cab_fecha'=>'required',
            'recep_cab_fecha_estimada'=>'required',
            'recep_cab_prioridad'=>'required',
            'recep_cab_kilometraje'=>'required',
            'recep_cab_nivel_combustible'=>'required',
            'recep_cab_estado'=>'required',
            'solicitudes_cab_id'=>'required',
            'clientes_id'=>'required',
            'tipo_servicio_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $recepcioncab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $recepcioncab
        ],200);
    }
    public function confirmar(Request $r, $id){
        $recepcioncab = RecepcionCab::find($id);
        if(!$recepcioncab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'recep_cab_observaciones'=>'required',
            'recep_cab_fecha'=>'required',
            'recep_cab_fecha_estimada'=>'required',
            'recep_cab_prioridad'=>'required',
            'recep_cab_kilometraje'=>'required',
            'recep_cab_nivel_combustible'=>'required',
            'recep_cab_estado'=>'required',
            'solicitudes_cab_id'=>'required',
            'clientes_id'=>'required',
            'tipo_servicio_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $recepcioncab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $recepcioncab
        ],200);
    }
    public function buscar(Request $r){
        return DB::select("SELECT 
            rc.id AS recep_cab_id,
            rc.recep_cab_observaciones,
            rc.recep_cab_estado,
            rc.recep_cab_prioridad,
            rc.recep_cab_kilometraje,
            rc.recep_cab_nivel_combustible,
            rc.user_id,
            u.name AS encargado,
            u.login,
            rc.created_at,
            rc.updated_at,

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
            rc.sucursal_id,
            s.suc_razon_social,
            rc.empresa_id,
            e.emp_razon_social,

            -- Texto descriptivo de la solicitud
            'RECEPCION NRO: ' || TO_CHAR(rc.id, '0000000') || 
            ' (' || rc.recep_cab_observaciones || ')' AS recepcion

        FROM 
            recep_cab rc 
        JOIN users u ON u.id = rc.user_id
        JOIN clientes c ON c.id = rc.clientes_id
        JOIN tipo_servicio ts ON ts.id = rc.tipo_servicio_id
        JOIN sucursal s ON s.empresa_id = rc.sucursal_id
        JOIN empresa e ON e.id = rc.empresa_id
        WHERE 
            rc.recep_cab_estado = 'CONFIRMADO'
    and rc.user_id = {$r->user_id} and u.name ilike'%{$r->name}%'");
    }
}
