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
        $r->validate([
            'pres_serv_cab_observaciones' => 'required|string|max:500|not_regex:/[*<>{}|]/',
            'pres_serv_cab_fecha'         => 'required|date_format:d/m/Y H:i:s',
            'pres_serv_cab_fecha_vence'   => 'required|date_format:d/m/Y H:i:s|after_or_equal:pres_serv_cab_fecha',
            'empresa_id'                  => 'required|integer|exists:empresa,id',
            'sucursal_id'                 => 'required|integer|exists:sucursal,id',
            'diagnostico_cab_id'          => 'required|integer|exists:diagnostico_cab,id',
            'tipo_servicio_id'            => 'required|integer|exists:tipo_servicio,id',
            'tipo_vehiculo_id'            => 'required|integer|exists:tipo_vehiculo,id',
            'clientes_id'                 => 'required|integer|exists:clientes,id',
            'promociones_cab_id'          => 'nullable|integer|exists:promociones_cab,id',
            'descuentos_cab_id'           => 'nullable|integer|exists:descuentos_cab,id',
        ], [
            'pres_serv_cab_observaciones.required'    => 'Las observaciones son obligatorias.',
            'pres_serv_cab_observaciones.max'         => 'Las observaciones no pueden superar 500 caracteres.',
            'pres_serv_cab_observaciones.not_regex'   => 'Las observaciones contienen caracteres no permitidos.',
            'pres_serv_cab_fecha.required'            => 'La fecha del presupuesto es obligatoria.',
            'pres_serv_cab_fecha.date_format'         => 'El formato de la fecha no es válido.',
            'pres_serv_cab_fecha_vence.required'      => 'La fecha de vencimiento es obligatoria.',
            'pres_serv_cab_fecha_vence.date_format'   => 'El formato de la fecha de vencimiento no es válido.',
            'pres_serv_cab_fecha_vence.after_or_equal'=> 'La fecha de vencimiento no puede ser anterior a la fecha del presupuesto.',
            'diagnostico_cab_id.required'             => 'Debe seleccionar un diagnóstico.',
            'tipo_servicio_id.required'               => 'Debe seleccionar el tipo de servicio.',
            'tipo_vehiculo_id.required'               => 'Debe seleccionar el tipo de vehículo.',
            'clientes_id.required'                    => 'Debe seleccionar un cliente.',
        ]);

        $diagnosticoParaRecep = \DB::table('diagnostico_cab')->where('id', $r->diagnostico_cab_id)->first();

        $presupuestoservcab = PresupuestoServCab::create([
            'pres_serv_cab_observaciones' => $r->pres_serv_cab_observaciones,
            'pres_serv_cab_fecha'         => $r->pres_serv_cab_fecha,
            'pres_serv_cab_fecha_vence'   => $r->pres_serv_cab_fecha_vence,
            'pres_serv_cab_estado'        => 'PENDIENTE',
            'empresa_id'                  => $r->empresa_id,
            'sucursal_id'                 => $r->sucursal_id,
            'diagnostico_cab_id'          => $r->diagnostico_cab_id,
            'recep_cab_id'                => $diagnosticoParaRecep->recep_cab_id ?? null,
            'tipo_servicio_id'            => $r->tipo_servicio_id,
            'tipo_vehiculo_id'            => $r->tipo_vehiculo_id,
            'clientes_id'                 => $r->clientes_id,
            'promociones_cab_id'          => $r->promociones_cab_id ?: null,
            'descuentos_cab_id'           => $r->descuentos_cab_id  ?: null,
            'funcionario_id'              => auth()->user()->funcionario_id,
        ]);
        $presupuestoservcab->save();

        $diagnosticocab = DiagnosticoCab::find($r->diagnostico_cab_id); // Cambiado a diagnostico_cab_id

        // Verificar si el presupuesto existe
        if (!$diagnosticocab) {
            return response()->json([
                'mensaje' => 'Diagnóstico no encontrado',
                'tipo' => 'error',
            ], 404);
        }
        $diagnosticocab->diag_cab_estado = "PROCESADO";
        $diagnosticocab->save();

        $detalles = DB::select("
            SELECT dd.item_id,
                   dd.diag_det_costo            AS pres_serv_det_costo,
                   dd.diag_det_cantidad         AS pres_serv_det_cantidad,
                   dd.diag_det_cantidad_stock   AS pres_serv_det_cantidad_stock,
                   dd.tipo_impuesto_id,
                   dd.marca_id,
                   dd.modelo_id
            FROM diagnostico_det dd
            JOIN items i ON i.id = dd.item_id
            WHERE dd.diagnostico_cab_id = ?
        ", [$diagnosticocab->id]);

        foreach ($detalles as $ocd) {
            $presupuestoservdet = new PresupuestoServDet();
            $presupuestoservdet->presupuesto_serv_cab_id    = $presupuestoservcab->id;
            $presupuestoservdet->item_id                    = $ocd->item_id;
            $presupuestoservdet->pres_serv_det_costo        = $ocd->pres_serv_det_costo;
            $presupuestoservdet->pres_serv_det_cantidad     = $ocd->pres_serv_det_cantidad;
            $presupuestoservdet->pres_serv_det_cantidad_stock = $ocd->pres_serv_det_cantidad_stock;
            $presupuestoservdet->tipo_impuesto_id           = $ocd->tipo_impuesto_id;
            $presupuestoservdet->marca_id                   = $ocd->marca_id  ?? null;
            $presupuestoservdet->modelo_id                  = $ocd->modelo_id ?? null;
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
        if (!$presupuestoservcab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }
        if ($presupuestoservcab->pres_serv_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden editar presupuestos en estado PENDIENTE.', 'tipo' => 'warning'], 400);
        }

        $r->validate([
            'pres_serv_cab_observaciones' => 'required|string|max:500|not_regex:/[*<>{}|]/',
            'pres_serv_cab_fecha'         => 'required|date_format:d/m/Y H:i:s',
            'pres_serv_cab_fecha_vence'   => 'required|date_format:d/m/Y H:i:s|after_or_equal:pres_serv_cab_fecha',
            'empresa_id'                  => 'required|integer|exists:empresa,id',
            'sucursal_id'                 => 'required|integer|exists:sucursal,id',
            'diagnostico_cab_id'          => 'required|integer|exists:diagnostico_cab,id',
            'tipo_servicio_id'            => 'required|integer|exists:tipo_servicio,id',
            'tipo_vehiculo_id'            => 'required|integer|exists:tipo_vehiculo,id',
            'clientes_id'                 => 'required|integer|exists:clientes,id',
            'promociones_cab_id'          => 'nullable|integer|exists:promociones_cab,id',
            'descuentos_cab_id'           => 'nullable|integer|exists:descuentos_cab,id',
        ]);

        $presupuestoservcab->update([
            'pres_serv_cab_observaciones' => $r->pres_serv_cab_observaciones,
            'pres_serv_cab_fecha'         => $r->pres_serv_cab_fecha,
            'pres_serv_cab_fecha_vence'   => $r->pres_serv_cab_fecha_vence,
            'empresa_id'                  => $r->empresa_id,
            'sucursal_id'                 => $r->sucursal_id,
            'diagnostico_cab_id'          => $r->diagnostico_cab_id,
            'tipo_servicio_id'            => $r->tipo_servicio_id,
            'tipo_vehiculo_id'            => $r->tipo_vehiculo_id,
            'clientes_id'                 => $r->clientes_id,
            'promociones_cab_id'          => $r->promociones_cab_id ?: null,
            'descuentos_cab_id'           => $r->descuentos_cab_id  ?: null,
        ]);

        return response()->json([
            'mensaje'  => 'Presupuesto modificado con éxito',
            'tipo'     => 'success',
            'registro' => $presupuestoservcab,
        ], 200);
    }
    public function anular(Request $r, $id)
    {
        $presupuestoservcab = PresupuestoServCab::find($id);
        if (!$presupuestoservcab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }
        if ($presupuestoservcab->pres_serv_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden anular presupuestos en estado PENDIENTE.', 'tipo' => 'warning'], 400);
        }

        $presupuestoservcab->update([
            'pres_serv_cab_estado' => 'ANULADO',
            'promociones_cab_id'   => null,
            'descuentos_cab_id'    => null,
        ]);

        $diagnostico = DiagnosticoCab::find($presupuestoservcab->diagnostico_cab_id);
        if ($diagnostico) {
            $diagnostico->diag_cab_estado = 'CONFIRMADO';
            $diagnostico->save();
        }

        return response()->json([
            'mensaje'  => 'Presupuesto anulado con éxito',
            'tipo'     => 'success',
            'registro' => $presupuestoservcab,
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $presupuestoservcab = PresupuestoServCab::find($id);
        if (!$presupuestoservcab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }
        if ($presupuestoservcab->pres_serv_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden confirmar presupuestos en estado PENDIENTE.', 'tipo' => 'warning'], 400);
        }

        $presupuestoservcab->update(['pres_serv_cab_estado' => 'CONFIRMADO']);

        return response()->json([
            'mensaje'  => 'Presupuesto confirmado con éxito',
            'tipo'     => 'success',
            'registro' => $presupuestoservcab,
        ], 200);
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

        WHERE psc.pres_serv_cab_estado = 'CONFIRMADO'
          AND psc.funcionario_id = ?
          AND (f.fun_nom || ' ' || f.fun_apellido) ILIKE ?

        ORDER BY
            CASE dg.diag_cab_prioridad
                WHEN 'ALTA'  THEN 1
                WHEN 'MEDIA' THEN 2
                WHEN 'BAJA'  THEN 3
                ELSE 4
            END,
            psc.id DESC
    ", [$r->funcionario_id, '%' . $r->name . '%']);
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
