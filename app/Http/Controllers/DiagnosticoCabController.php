<?php

namespace App\Http\Controllers;
use App\Models\DiagnosticoCab;
use App\Models\RecepcionCab;
use App\Models\DiagnosticoDet;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class DiagnosticoCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
            dc.id,
            dc.diag_cab_fecha,
            dc.diag_cab_prioridad,
            dc.diag_cab_estado,
            dc.diag_cab_observaciones,
            dc.diag_cab_kilometraje,
            dc.diag_cab_nivel_combustible,

            -- Cliente
            c.id AS clientes_id,
            c.cli_nombre,
            c.cli_apellido,       
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- Sucursal y Empresa
            rc.sucursal_id,
            s.suc_razon_social,
            rc.empresa_id,
            e.emp_razon_social,

            -- Recepción
            rc.id AS recep_cab_id,
            rc.recep_cab_fecha,
            rc.recep_cab_prioridad,
            rc.recep_cab_observaciones,

            -- Tipo de diagnóstico
            td.id AS tipo_diagnostico_id,
            td.tipo_diag_nombre,

            -- Tipo de vehículo (CORRECTO)
            tv.id AS tipo_vehiculo_id,
            tv.tip_veh_nombre,
            tv.tip_veh_capacidad,
            tv.tip_veh_combustible,
            tv.tip_veh_categoria,
            tv.tip_veh_observacion,

            -- Marca y modelo (CORRECTO)
            m.marc_nom as marca_nombre,
            mo.modelo_nom as modelo_nombre,

            -- Tipo de servicio correcto
            ts.id AS tipo_servicio_id,
            ts.tipo_serv_nombre,

            -- Texto de recepción
            'RECEPCION NRO: ' || to_char(rc.id, '0000000') AS recepcion,

            -- Encargado
            u.name AS encargado

        FROM diagnostico_cab dc 
        JOIN users u ON u.id = dc.user_id
        JOIN sucursal s ON s.empresa_id = dc.sucursal_id
        JOIN empresa e ON e.id = dc.empresa_id

        JOIN recep_cab rc ON rc.id = dc.recep_cab_id
        JOIN clientes c ON c.id = rc.clientes_id

        LEFT JOIN tipo_diagnostico td ON td.id = dc.tipo_diagnostico_id
        LEFT JOIN tipo_servicio ts ON ts.id = rc.tipo_servicio_id

        -- 🔥 AQUÍ LOS JOIN CORRECTOS DEL VEHÍCULO 🔥
        LEFT JOIN tipo_vehiculo tv ON tv.id = rc.tipo_vehiculo_id
        LEFT JOIN marca m ON m.id = tv.marca_id
        LEFT JOIN modelo mo ON mo.id = tv.modelo_id

        ORDER BY dc.id DESC
    ");
}


public function store(Request $r){
        $datosValidados = $r->validate([
            'diag_cab_observaciones'=>'required',
            'diag_cab_fecha'=>'required',
            'diag_cab_prioridad'=>'required',
            'diag_cab_kilometraje'=>'required',
            'diag_cab_nivel_combustible'=>'required',
            'diag_cab_estado'=>'required',
            'recep_cab_id'=>'required',
            'clientes_id'=>'required',
            'tipo_diagnostico_id'=>'required',
            'tipo_vehiculo_id'=>'required',
            'tipo_servicio_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $diagnosticocab = DiagnosticoCab::create($datosValidados);
        $diagnosticocab->save();

        $recepcioncab = RecepcionCab::find($r->recep_cab_id); // Cambiado a presupuestos_id

        // Verificar si el presupuesto existe
        if (!$recepcioncab) {
            return response()->json([
                'mensaje' => 'Recepcion no encontrada',
                'tipo' => 'error',
            ], 404);
        }
        $recepcioncab->recep_cab_estado = "PROCESADO"; // Cambiado a pre_estado
        $recepcioncab->save();

        // Lógica para guardar detalles
    $detalles = DB::select("SELECT 
    rd.*, 
    i.item_decripcion,
    rd.recep_det_costo as diag_det_costo,
    rd.recep_det_cantidad as diag_det_cantidad,
    rd.recep_det_cantidad_stock as diag_det_cantidad_stock,
    i.tipo_impuesto_id
    FROM recep_det rd 
    JOIN items i ON i.id = rd.item_id 
    WHERE rd.recep_cab_id = $recepcioncab->id;");

    foreach ($detalles as $dd) {
        $diagnosticodet = new DiagnosticoDet();
        $diagnosticodet->diagnostico_cab_id = $diagnosticocab->id;
        $diagnosticodet->item_id = $dd->item_id;
        $diagnosticodet->diag_det_costo = $dd->diag_det_costo;
        $diagnosticodet->diag_det_cantidad = $dd->diag_det_cantidad;
        $diagnosticodet->diag_det_cantidad_stock = $dd->diag_det_cantidad_stock;
        $diagnosticodet->tipo_impuesto_id = $dd->tipo_impuesto_id; 
        $diagnosticodet->save();
    }
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $diagnosticocab
        ],200);
    }
    public function update(Request $r, $id){
        $diagnosticocab = DiagnosticoCab::find($id);
        if(!$diagnosticocab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'diag_cab_observaciones'=>'required',
            'diag_cab_fecha'=>'required',
            'diag_cab_prioridad'=>'required',
            'diag_cab_kilometraje'=>'required',
            'diag_cab_nivel_combustible'=>'required',
            'diag_cab_estado'=>'required',
            'recep_cab_id'=>'required',
            'clientes_id'=>'required',
            'tipo_diagnostico_id'=>'required',
            'tipo_vehiculo_id'=>'required',
            'tipo_servicio_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $diagnosticocab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $diagnosticocab
        ],200);
    }
    public function anular(Request $r, $id){
        $diagnosticocab = DiagnosticoCab::find($id);
        if(!$diagnosticocab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'diag_cab_observaciones'=>'required',
            'diag_cab_fecha'=>'required',
            'diag_cab_prioridad'=>'required',
            'diag_cab_kilometraje'=>'required',
            'diag_cab_nivel_combustible'=>'required',
            'diag_cab_estado'=>'required',
            'recep_cab_id'=>'required',
            'clientes_id'=>'required',
            'tipo_diagnostico_id'=>'required',
            'tipo_vehiculo_id'=>'required',
            'tipo_servicio_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $diagnosticocab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $diagnosticocab
        ],200);
    }
    public function confirmar(Request $r, $id){
        $diagnosticocab = DiagnosticoCab::find($id);
        if(!$diagnosticocab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'diag_cab_observaciones'=>'required',
            'diag_cab_fecha'=>'required',
            'diag_cab_prioridad'=>'required',
            'diag_cab_kilometraje'=>'required',
            'diag_cab_nivel_combustible'=>'required',
            'diag_cab_estado'=>'required',
            'recep_cab_id'=>'required',
            'clientes_id'=>'required',
            'tipo_diagnostico_id'=>'required',
            'tipo_vehiculo_id'=>'required',
            'tipo_servicio_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $diagnosticocab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $diagnosticocab
        ],200);
    }
    public function buscar(Request $r)
{
    $texto  = $r->input('texto');
    $userId = $r->input('user_id');

    return DB::select("
        SELECT 
            dc.id AS diagnostico_cab_id,
            TO_CHAR(dc.diag_cab_fecha, 'DD/MM/YYYY HH24:MI:SS') AS diag_cab_fecha,
            dc.diag_cab_prioridad,
            dc.diag_cab_estado,
            dc.diag_cab_observaciones,
            dc.diag_cab_kilometraje,
            dc.diag_cab_nivel_combustible,

            -- Cliente
            c.id AS clientes_id,
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- Tipo de diagnóstico
            td.id AS tipo_diagnostico_id,
            td.tipo_diag_nombre AS tipo_diag_nombre,

            -- Tipo de servicio (AHORA DESDE DIAGNÓSTICO)
            ts.id AS tipo_servicio_id,
            ts.tipo_serv_nombre AS tipo_serv_nombre,

            -- Empresa y sucursal
            dc.empresa_id,
            e.emp_razon_social,
            dc.sucursal_id,
            s.suc_razon_social,

            -- Encargado
            u.id AS user_id,
            u.name AS encargado,

            tv.id AS tipo_vehiculo_id,
            tv.tip_veh_nombre,
            tv.tip_veh_capacidad,
            tv.tip_veh_combustible,
            tv.tip_veh_categoria,

            m.marc_nom,
            mo.modelo_nom,

            -- Texto descriptivo
            'DIAGNOSTICO NRO: ' || TO_CHAR(dc.id, '0000000') ||
            ' - Cliente: ' || c.cli_nombre || ' ' || c.cli_apellido ||
            ' (' || td.tipo_diag_nombre || ')' AS diagnostico

        FROM diagnostico_cab dc
        JOIN users u        ON u.id = dc.user_id
        JOIN empresa e      ON e.id = dc.empresa_id
        JOIN sucursal s     ON s.empresa_id = dc.sucursal_id
        JOIN clientes c     ON c.id = dc.clientes_id

        LEFT JOIN tipo_diagnostico td ON td.id = dc.tipo_diagnostico_id

        -- 🔹 Tipo de servicio desde DIAGNÓSTICO
        LEFT JOIN tipo_servicio ts ON ts.id = dc.tipo_servicio_id

        -- 🔹 Tipo de vehículo desde DIAGNÓSTICO
        LEFT JOIN tipo_vehiculo tv ON tv.id = dc.tipo_vehiculo_id
        LEFT JOIN marca m          ON m.id = tv.marca_id
        LEFT JOIN modelo mo        ON mo.id = tv.modelo_id

        WHERE 
            dc.diag_cab_estado IN ('CONFIRMADO')
            AND u.id = {$userId}
            AND (
                c.cli_nombre ILIKE '%{$texto}%'
                OR c.cli_apellido ILIKE '%{$texto}%'
                OR c.cli_ruc ILIKE '%{$texto}%'
                OR td.tipo_diag_nombre ILIKE '%{$texto}%'
                OR ts.tipo_serv_nombre ILIKE '%{$texto}%'
                OR TO_CHAR(dc.id, '0000000') ILIKE '%{$texto}%'
            )
        ORDER BY dc.id DESC
    ");
}
}
