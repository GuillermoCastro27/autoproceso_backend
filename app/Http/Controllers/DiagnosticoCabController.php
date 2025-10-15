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

            -- Sucursal y Empresa (según tu modelo)
            rc.sucursal_id,
            s.suc_razon_social,
            rc.empresa_id,
            e.emp_razon_social,

            -- Recepcion
            rc.id AS recep_cab_id,
            rc.recep_cab_fecha,
            rc.recep_cab_prioridad AS recep_cab_prioridad,
            rc.recep_cab_observaciones AS recep_cab_observaciones,

            -- Tipo de servicio
            ts.id AS tipo_servicio_id,
            ts.tipo_serv_nombre AS tipo_servicio,

            -- Texto para mostrar solicitud
           'RECEPCION NRO: ' || to_char(rc.id, '0000000') AS recepcion,

            -- Encargado
            u.name AS encargado

        FROM diagnostico_cab dc 
        JOIN users u ON u.id = dc.user_id
        JOIN sucursal s ON s.empresa_id = dc.sucursal_id  -- 🔹 Mantenido según tu modelo
        JOIN empresa e ON e.id = dc.empresa_id
        JOIN recep_cab rc ON rc.id = dc.recep_cab_id
        JOIN clientes c ON c.id = rc.clientes_id
        LEFT JOIN tipo_servicio ts ON ts.id = dc.tipo_servicio_id
        ORDER BY dc.id desc
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
}
