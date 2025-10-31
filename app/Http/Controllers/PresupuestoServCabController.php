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
    public function anular(Request $r, $id)
    {
        $presupuestoservcab = PresupuestoServCab::find($id);
        if (!$presupuestoservcab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        // ⚠️ Solo validamos los campos realmente necesarios
        $datosValidados = $r->validate([
            'pres_serv_cab_observaciones' => 'required',
            'pres_serv_cab_fecha' => 'required',
            'pres_serv_cab_fecha_vence' => 'required',
            'pres_serv_cab_estado' => 'required',
            'user_id' => 'required',
            'empresa_id' => 'required',
            'sucursal_id' => 'required',
            'diagnostico_cab_id' => 'required',
            'clientes_id' => 'required'
        ]);

        // 💡 Asignamos NULL a los campos de FK opcionales (evita error FK)
        $presupuestoservcab->update([
            ...$datosValidados,
            'promociones_cab_id' => $r->input('promociones_cab_id') ?: null,
            'descuentos_cab_id'  => $r->input('descuentos_cab_id') ?: null,
            'pres_serv_cab_estado' => 'ANULADO',
            'updated_at' => now(),
        ]);

        return response()->json([
            'mensaje' => 'Presupuesto anulado con éxito',
            'tipo' => 'success',
            'registro' => $presupuestoservcab
        ], 200);
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
            'promociones_cab_id'=>'nullable|integer',
            'descuentos_cab_id'=>'nullable|integer',
            'clientes_id'=>'required'
        ]);
        $presupuestoservcab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $presupuestoservcab
        ],200);
    }
    public function buscar(Request $r)
{
    return DB::select("
        SELECT 
            psc.id AS presupuestos_serv_cab_id,
            psc.pres_serv_cab_observaciones,
            psc.pres_serv_cab_fecha,
            psc.pres_serv_cab_fecha_vence,
            psc.pres_serv_cab_estado,
            psc.user_id,
            u.name AS encargado,
            u.login,

            -- 🧾 Cliente
            c.id AS clientes_id,
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- 🧰 Diagnóstico
            dg.id AS diagnostico_cab_id,
            dg.diag_cab_fecha,
            dg.diag_cab_kilometraje,
            dg.diag_cab_prioridad,
            dg.diag_cab_observaciones,
            dg.diag_cab_nivel_combustible,

            -- 🧩 Tipo de diagnóstico / servicio
            ts.id AS tipo_servicio_id,
            ts.tipo_serv_nombre AS tipo_serv_nombre,

            -- 🏢 Empresa y sucursal
            psc.empresa_id,
            e.emp_razon_social,
            psc.sucursal_id,
            s.suc_razon_social,

            -- 🔢 Texto descriptivo para mostrar en lista
            'PRESUPUESTO NRO: ' || TO_CHAR(psc.id, '0000000') || 
            ' (' || c.cli_nombre || ' ' || c.cli_apellido || ')' AS presupuesto_serv

        FROM 
            presupuesto_serv_cab psc
        JOIN users u ON u.id = psc.user_id
        JOIN empresa e ON e.id = psc.empresa_id
        JOIN sucursal s ON s.empresa_id = psc.sucursal_id
        JOIN diagnostico_cab dg ON dg.id = psc.diagnostico_cab_id
        JOIN clientes c ON c.id = dg.clientes_id
        JOIN tipo_servicio ts ON ts.id = dg.tipo_servicio_id

        WHERE 
            psc.pres_serv_cab_estado = 'CONFIRMADO'
        AND psc.user_id = {$r->user_id}
        AND u.name ILIKE '%{$r->name}%'

        ORDER BY psc.id DESC
    ");
}
}
