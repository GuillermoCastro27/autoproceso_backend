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

            -- Cliente
            c.id AS clientes_id,
            c.cli_nombre,
            c.cli_apellido,       
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- Empresa y Sucursal
            psc.empresa_id,
            e.emp_razon_social,
            psc.sucursal_id,
            s.suc_razon_social,

            -- Diagnóstico
            dg.id AS diagnostico_cab_id,
            dg.diag_cab_fecha,
            dg.diag_cab_kilometraje,
            dg.diag_cab_prioridad,
            dg.diag_cab_observaciones,
            dg.diag_cab_nivel_combustible,

            -- Tipo Diagnóstico
            td2.id AS tipo_diagnostico_id,
            td2.tipo_diag_nombre AS tipo_diag_nombre,

            -- Texto del diagnóstico
            'DIAGNOSTICO NRO: ' || TO_CHAR(dg.id, '0000000') AS diagnostico,

            -- Usuario encargado
            f.fun_nom || ' ' || f.fun_apellido AS funcionario,

            -- Tipo de Vehículo
            tv.id AS tipo_vehiculo_id,
            tv.tip_veh_nombre,
            tv.tip_veh_capacidad,
            tv.tip_veh_combustible,
            tv.tip_veh_categoria,

            -- Marca y Modelo
            m.id AS marca_id,
            m.marc_nom AS marca_nombre,
            mo.id AS modelo_id,
            mo.modelo_nom AS modelo_nombre,

            -- Tipo de Servicio (AHORA SÍ: solo nombre y precio)
            ts.id AS tipo_servicio_id,
            ts.tipo_serv_nombre AS tipo_servicio,
            ts.tip_serv_precio AS precio_servicio,

            -- Descuento (opcional)
            COALESCE(dc.id, 0) AS descuentos_cab_id,
            COALESCE(dc.desc_cab_nombre, 'N/A') AS desc_cab_nombre,
            COALESCE(dc.desc_cab_porcentaje, 0) AS desc_cab_porcentaje,
            COALESCE(td.tipo_desc_nombre, 'N/A') AS tipo_descuentos,

            -- Promoción (opcional)
            COALESCE(pc.id, 0) AS promociones_cab_id,
            COALESCE(pc.prom_cab_nombre, 'N/A') AS prom_cab_nombre,
            COALESCE(tp.tipo_prom_nombre, 'N/A') AS tipo_promociones,
            COALESCE(tp.tipo_prom_modo, 'N/A') AS tipo_prom_modo,
            COALESCE(tp.tipo_prom_valor, 0) AS tipo_prom_valor

        FROM presupuesto_serv_cab psc
        JOIN funcionario f       ON f.id = psc.funcionario_id
        JOIN empresa e           ON e.id = psc.empresa_id
        JOIN sucursal s          ON s.id = psc.sucursal_id
        JOIN diagnostico_cab dg  ON dg.id = psc.diagnostico_cab_id
        JOIN clientes c          ON c.id = dg.clientes_id
        JOIN tipo_diagnostico td2 ON td2.id = dg.tipo_diagnostico_id

        -- Vehículo
        JOIN tipo_vehiculo tv    ON tv.id = psc.tipo_vehiculo_id
        JOIN marca m             ON m.id = tv.marca_id
        JOIN modelo mo           ON mo.id = tv.modelo_id

        -- Tipo de servicio
        JOIN tipo_servicio ts    ON ts.id = psc.tipo_servicio_id

        -- Relaciones opcionales
        LEFT JOIN descuentos_cab dc    ON dc.id = psc.descuentos_cab_id
        LEFT JOIN tipo_descuentos td   ON td.id = dc.tipo_descuentos_id 
        LEFT JOIN promociones_cab pc   ON pc.id = psc.promociones_cab_id
        LEFT JOIN tipo_promociones tp  ON tp.id = pc.tipo_promociones_id 

        ORDER BY psc.id DESC
    ");
}


public function store(Request $r){
        $datosValidados = $r->validate([
            'pres_serv_cab_observaciones'=>'required',
            'pres_serv_cab_fecha'=>'required',
            'pres_serv_cab_fecha_vence'=>'required',
            'pres_serv_cab_estado'=>'required',
            'funcionario_id'=>'nullable',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'diagnostico_cab_id'=>'required',
            'tipo_servicio_id'=>'required',
            'tipo_vehiculo_id'=>'required',
            'promociones_cab_id'=>'nullable|integer',
            'descuentos_cab_id'=>'nullable|integer',
            'clientes_id'=>'required'
        ]);
        $datosValidados['funcionario_id'] = auth()->user()->funcionario_id;
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
            'funcionario_id'=>'nullable',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'diagnostico_cab_id'=>'required',
            'tipo_servicio_id'=>'required',
            'tipo_vehiculo_id'=>'required',
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
            'funcionario_id' => 'nullable',
            'empresa_id' => 'required',
            'sucursal_id' => 'required',
            'diagnostico_cab_id' => 'required',
            'tipo_servicio_id'=>'required',
            'tipo_vehiculo_id'=>'required',
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

        // Revertir DiagnosticoCab a CONFIRMADO
        $diagnostico = DiagnosticoCab::find($presupuestoservcab->diagnostico_cab_id);
        if ($diagnostico) {
            $diagnostico->diag_cab_estado = 'CONFIRMADO';
            $diagnostico->save();
        }

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
            'funcionario_id'=>'nullable',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'diagnostico_cab_id'=>'required',
            'tipo_servicio_id'=>'required',
            'tipo_vehiculo_id'=>'required',
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
            psc.funcionario_id,
            f.fun_nom || ' ' || f.fun_apellido AS encargado,

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
            td.id AS tipo_diagnostico_id,
            td.tipo_diag_nombre AS tipo_diag_nombre,

            -- Tipo de Vehículo
            tv.id AS tipo_vehiculo_id,
            tv.tip_veh_nombre,
            tv.tip_veh_capacidad,
            tv.tip_veh_combustible,
            tv.tip_veh_categoria,

            -- Marca y Modelo
            m.id AS marca_id,
            m.marc_nom AS marc_nom,
            mo.id AS modelo_id,
            mo.modelo_nom AS modelo_nom,

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
        JOIN funcionario f ON f.id = psc.funcionario_id
        JOIN empresa e ON e.id = psc.empresa_id
        JOIN sucursal s ON s.id = psc.sucursal_id
        JOIN diagnostico_cab dg ON dg.id = psc.diagnostico_cab_id
        JOIN clientes c ON c.id = psc.clientes_id
        JOIN tipo_diagnostico td ON td.id = dg.tipo_diagnostico_id
        JOIN tipo_vehiculo tv    ON tv.id = psc.tipo_vehiculo_id
        JOIN marca m             ON m.id = tv.marca_id
        JOIN modelo mo           ON mo.id = tv.modelo_id

        WHERE 
            psc.pres_serv_cab_estado = 'CONFIRMADO'
        AND psc.funcionario_id = {$r->funcionario_id}
        AND (f.fun_nom || ' ' || f.fun_apellido) ILIKE '%{$r->name}%'

        ORDER BY psc.id DESC
    ");
}
public function readById($id)
{
    $registro = DB::selectOne("
        SELECT 
            -- =========================
            -- CABECERA
            -- =========================
            psc.id,
            psc.pres_serv_cab_fecha,
            psc.pres_serv_cab_fecha_vence,
            psc.pres_serv_cab_observaciones,
            psc.pres_serv_cab_estado,

            -- =========================
            -- EMPRESA / SUCURSAL
            -- =========================
            psc.empresa_id,
            e.emp_razon_social,

            psc.sucursal_id,
            s.suc_razon_social,

            -- =========================
            -- CLIENTE
            -- =========================
            psc.clientes_id,
            c.cli_nombre,
            c.cli_apellido,

            -- =========================
            -- RELACIONES CLAVE
            -- =========================
            psc.diagnostico_cab_id,
            psc.tipo_servicio_id,
            psc.tipo_vehiculo_id,

            -- =========================
            -- PROMOCIÓN (opcional)
            -- =========================
            COALESCE(pc.id, 0) AS promociones_cab_id,
            COALESCE(tp.tipo_prom_modo, 'N/A') AS tipo_prom_modo,
            COALESCE(tp.tipo_prom_valor, 0) AS tipo_prom_valor,

            -- =========================
            -- DESCUENTO (opcional)
            -- =========================
            COALESCE(dc.id, 0) AS descuentos_cab_id,
            COALESCE(dc.desc_cab_porcentaje, 0) AS desc_cab_porcentaje,

            -- =========================
            -- MANO DE OBRA (tipo servicio)
            -- =========================
            COALESCE(ts.tip_serv_precio, 0) AS tip_serv_precio

        FROM presupuesto_serv_cab psc

        -- Relaciones obligatorias
        JOIN empresa e   ON e.id = psc.empresa_id
        JOIN sucursal s  ON s.id = psc.sucursal_id
        JOIN clientes c  ON c.id = psc.clientes_id

        -- Relaciones opcionales
        LEFT JOIN promociones_cab pc     ON pc.id = psc.promociones_cab_id
        LEFT JOIN tipo_promociones tp    ON tp.id = pc.tipo_promociones_id
        LEFT JOIN descuentos_cab dc      ON dc.id = psc.descuentos_cab_id
        LEFT JOIN tipo_servicio ts       ON ts.id = psc.tipo_servicio_id

        WHERE psc.id = ?
    ", [$id]);

    if (!$registro) {
        return response()->json([
            'mensaje' => 'Presupuesto no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    return response()->json($registro);
}


}
