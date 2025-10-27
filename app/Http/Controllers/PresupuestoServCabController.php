<?php

namespace App\Http\Controllers;

use App\Models\PresupuestoServCab;
use App\Models\DiagnosticoCab;
use App\Models\PresupuestoServDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PresupuestoServCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
            psc.id,
            psc.pres_serv_cab_observaciones,
            psc.pres_serv_cab_fecha,
            psc.pres_serv_cab_fecha_vence,
            psc.pres_serv_cab_estado,

            -- 🧾 Cliente
            c.id AS clientes_id,
            c.cli_nombre,
            c.cli_apellido,       
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- 🏢 Sucursal y Empresa
            psc.sucursal_id,
            s.suc_razon_social,
            psc.empresa_id,
            e.emp_razon_social,

            -- 🧰 Diagnóstico
            dg.id AS diagnostico_cab_id,
            dg.diag_cab_fecha,
            dg.diag_cab_kilometraje,
            dg.diag_cab_prioridad,
            dg.diag_cab_observaciones,
            dg.diag_cab_nivel_combustible,

            -- 💸 Descuento (opcional)
            COALESCE(dc.id, 0) AS descuentos_cab_id,
            COALESCE(dc.desc_cab_nombre, 'N/A') AS desc_cab_nombre,
            COALESCE(dc.desc_cab_porcentaje, 0) AS desc_cab_porcentaje,
            COALESCE(td.tipo_desc_nombre, 'N/A') AS tipo_descuentos,

            -- 🎁 Promoción (opcional)
            COALESCE(pc.id, 0) AS promociones_cab_id,
            COALESCE(pc.prom_cab_nombre, 'N/A') AS prom_cab_nombre,
            COALESCE(tp.tipo_prom_nombre, 'N/A') AS tipo_promociones,
            COALESCE(tp.tipo_prom_modo, 'N/A') AS tipo_prom_modo,
            COALESCE(tp.tipo_prom_valor, 0) AS tipo_prom_valor,

            -- 🧩 Tipo de servicio
            ts.id AS tipo_servicio_id,
            ts.tipo_serv_nombre AS tipo_servicio,

            -- 🩺 Texto descriptivo del diagnóstico
            'DIAGNOSTICO NRO: ' || TO_CHAR(dg.id, '0000000') AS diagnostico,

            -- 👤 Usuario encargado
            u.name AS encargado

        FROM presupuesto_serv_cab psc 
        JOIN users u ON u.id = psc.user_id 
        JOIN empresa e ON e.id = psc.empresa_id
        JOIN sucursal s ON s.empresa_id = psc.sucursal_id
        JOIN diagnostico_cab dg ON dg.id = psc.diagnostico_cab_id
        JOIN clientes c ON c.id = dg.clientes_id
        JOIN tipo_servicio ts ON ts.id = dg.tipo_servicio_id

        -- 🔹 Relaciones opcionales
        LEFT JOIN descuentos_cab dc ON dc.id = psc.descuentos_cab_id
        LEFT JOIN tipo_descuentos td ON td.id = dc.tipo_descuentos_id 
        LEFT JOIN promociones_cab pc ON pc.id = psc.promociones_cab_id
        LEFT JOIN tipo_promociones tp ON tp.id = pc.tipo_promociones_id 

        ORDER BY psc.id DESC
    ");
}

public function store(Request $r){
        $datosValidados = $r->validate([
            'pres_serv_cab_observaciones'=>'required',
            'pres_serv_cab_fecha'=>'required',
            'pres_serv_cab_fecha_vence'=>'required',
            'pres_serv_cab_estado'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'diagnostico_cab_id'=>'required',
            'promociones_cab_id'=>'nullable|integer',
            'descuentos_cab_id'=>'nullable|integer',
            'clientes_id'=>'required'
        ]);
        $presupuestoservcab = PresupuestoServCab::create($datosValidados);
        $presupuestoservcab->save();

        $diagnosticocab = DiagnosticoCab::find($r->diagnostico_cab_id); // Cambiado a diagnostico_cab_id

        // Verificar si el presupuesto existe
        if (!$diagnosticocab) {
            return response()->json([
                'mensaje' => 'Diagnóstico no encontrado',
                'tipo' => 'error',
            ], 404);
        }
        $diagnosticocab->diag_cab_estado = "PROCESADO"; // Cambiado a pre_estado
        $diagnosticocab->save();

    $detalles = DB::select("SELECT 
    dd.*, 
    i.item_decripcion,
    dd.diag_det_costo  as pres_serv_det_costo,
    dd.diag_det_cantidad  as pres_serv_det_cantidad,
    dd.diag_det_cantidad_stock as pres_serv_det_cantidad_stock,
    i.tipo_impuesto_id
    FROM  diagnostico_det dd 
    JOIN items i ON i.id = dd.item_id 
    WHERE dd.diagnostico_cab_id  = $diagnosticocab->id;");

    foreach ($detalles as $ocd) {
        $presupuestoservdet = new PresupuestoServDet();
        $presupuestoservdet->presupuesto_serv_cab_id = $presupuestoservcab->id;
        $presupuestoservdet->item_id = $ocd->item_id;
        $presupuestoservdet->pres_serv_det_costo = $ocd->pres_serv_det_costo;
        $presupuestoservdet->pres_serv_det_cantidad = $ocd->pres_serv_det_cantidad;
        $presupuestoservdet->pres_serv_det_cantidad_stock = $ocd->pres_serv_det_cantidad_stock;
        $presupuestoservdet->tipo_impuesto_id = $ocd->tipo_impuesto_id; 
        $presupuestoservdet->save();
    }
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $presupuestoservcab
        ],200);
    }
    public function update(Request $r, $id){
        $presupuestoservcab = PresupuestoServCab::find($id);
        if(!$presupuestoservcab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'pres_serv_cab_observaciones'=>'required',
            'pres_serv_cab_fecha'=>'required',
            'pres_serv_cab_fecha_vence'=>'required',
            'pres_serv_cab_estado'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'diagnostico_cab_id'=>'required',
            'promociones_cab_id'=>'required',
            'descuentos_cab_id'=>'required',
            'clientes_id'=>'required'
        ]);
        $presupuestoservcab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $presupuestoservcab
        ],200);
    }
    public function anular(Request $r, $id){
        $presupuestoservcab = PresupuestoServCab::find($id);
        if(!$presupuestoservcab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'pres_serv_cab_observaciones'=>'required',
            'pres_serv_cab_fecha'=>'required',
            'pres_serv_cab_fecha_vence'=>'required',
            'pres_serv_cab_estado'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'diagnostico_cab_id'=>'required',
            'promociones_cab_id'=>'required',
            'descuentos_cab_id'=>'required',
            'clientes_id'=>'required'
        ]);
        $presupuestoservcab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $presupuestoservcab
        ],200);
    }
    public function confirmar(Request $r, $id){
        $presupuestoservcab = PresupuestoServCab::find($id);
        if(!$presupuestoservcab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'pres_serv_cab_observaciones'=>'required',
            'pres_serv_cab_fecha'=>'required',
            'pres_serv_cab_fecha_vence'=>'required',
            'pres_serv_cab_estado'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'diagnostico_cab_id'=>'required',
            'promociones_cab_id'=>'required',
            'descuentos_cab_id'=>'required',
            'clientes_id'=>'required'
        ]);
        $presupuestoservcab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $presupuestoservcab
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
