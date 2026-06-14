<?php

namespace App\Http\Controllers;

use App\Models\OrdenServCab;
use App\Models\OrdenServDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrdenServCabController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                osc.id,
                osc.ord_serv_fecha,
                osc.ord_serv_fecha_vence,
                osc.ord_serv_observaciones,
                osc.ord_serv_estado,
                osc.ord_serv_tipo,

                c.id AS clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                c.cli_correo,

                dg.id AS diagnostico_cab_id,
                dg.diag_cab_observaciones AS diagnostico,

                td.id AS tipo_diagnostico_id,
                td.tipo_diag_nombre,

                et.id AS equipo_trabajo_id,
                et.equipo_nombre,
                et.equipo_descripcion,
                et.equipo_categoria,

                tv.id AS tipo_vehiculo_id,
                tv.tip_veh_nombre,
                tv.tip_veh_capacidad,
                tv.tip_veh_combustible,
                tv.tip_veh_categoria,

                m.id  AS marca_id,
                m.marc_nom,
                mo.id AS modelo_id,
                mo.modelo_nom,

                psc.id AS presupuesto_serv_cab_id,
                'PRESUPUESTO SERV Nº: ' || to_char(psc.id, '0000000')
                    || ' (' || COALESCE(psc.pres_serv_cab_observaciones, 'N/A') || ')'
                    AS presupuesto_serv,

                osc.empresa_id,
                e.emp_razon_social,
                osc.sucursal_id,
                s.suc_razon_social,

                f.fun_nom || ' ' || f.fun_apellido AS funcionario

            FROM orden_serv_cab osc
            JOIN funcionario f            ON f.id  = osc.funcionario_id
            JOIN empresa e                ON e.id  = osc.empresa_id
            JOIN sucursal s               ON s.id  = osc.sucursal_id
            JOIN clientes c               ON c.id  = osc.clientes_id
            JOIN diagnostico_cab dg       ON dg.id = osc.diagnostico_cab_id
            JOIN tipo_diagnostico td      ON td.id = osc.tipo_diagnostico_id
            JOIN equipo_trabajo et        ON et.id = osc.equipo_trabajo_id
            JOIN tipo_vehiculo tv         ON tv.id = osc.tipo_vehiculo_id
            JOIN marca m                  ON m.id  = tv.marca_id
            JOIN modelo mo                ON mo.id = tv.modelo_id
            JOIN presupuesto_serv_cab psc ON psc.id = osc.presupuesto_serv_cab_id

            ORDER BY osc.id DESC
        ");
    }

    public function store(Request $r)
    {
        $r->validate([
            'ord_serv_observaciones'  => ['required', 'string', 'max:500', 'not_regex:/[*<>{}|]/'],
            'ord_serv_fecha'          => 'required|date_format:d/m/Y H:i:s',
            'ord_serv_fecha_vence'    => 'required|date_format:d/m/Y H:i:s|after_or_equal:ord_serv_fecha',
            'ord_serv_tipo'           => 'required|string|max:50',
            'presupuesto_serv_cab_id' => 'required|integer|exists:presupuesto_serv_cab,id',
            'clientes_id'             => 'required|integer|exists:clientes,id',
            'empresa_id'              => 'required|integer|exists:empresa,id',
            'sucursal_id'             => 'required|integer|exists:sucursal,id',
            'diagnostico_cab_id'      => 'required|integer|exists:diagnostico_cab,id',
            'tipo_diagnostico_id'     => 'required|integer|exists:tipo_diagnostico,id',
            'tipo_vehiculo_id'        => 'required|integer|exists:tipo_vehiculo,id',
            'equipo_trabajo_id'       => 'required|integer|exists:equipo_trabajo,id',
        ], [
            'ord_serv_observaciones.required'     => 'Las observaciones son obligatorias.',
            'ord_serv_observaciones.not_regex'    => 'Las observaciones contienen caracteres no permitidos.',
            'ord_serv_fecha.required'             => 'La fecha es obligatoria.',
            'ord_serv_fecha.date_format'          => 'El formato de fecha no es válido (DD/MM/YYYY HH:MM:SS).',
            'ord_serv_fecha_vence.required'       => 'La fecha de vencimiento es obligatoria.',
            'ord_serv_fecha_vence.date_format'    => 'El formato de fecha de vencimiento no es válido.',
            'ord_serv_fecha_vence.after_or_equal' => 'La fecha de vencimiento debe ser mayor o igual a la fecha de inicio.',
            'presupuesto_serv_cab_id.required'    => 'Debe seleccionar un presupuesto de servicio.',
            'presupuesto_serv_cab_id.exists'      => 'El presupuesto seleccionado no es válido.',
            'clientes_id.required'                => 'Debe seleccionar un cliente.',
            'empresa_id.required'                 => 'La empresa es obligatoria.',
            'sucursal_id.required'                => 'La sucursal es obligatoria.',
            'diagnostico_cab_id.required'         => 'El diagnóstico es obligatorio.',
            'tipo_diagnostico_id.required'        => 'El tipo de diagnóstico es obligatorio.',
            'tipo_vehiculo_id.required'           => 'El tipo de vehículo es obligatorio.',
            'equipo_trabajo_id.required'          => 'Debe seleccionar un equipo de trabajo.',
        ]);

        $presupuestoParaRecep = \DB::table('presupuesto_serv_cab')->where('id', $r->presupuesto_serv_cab_id)->first();

        $ordenservcab = OrdenServCab::create([
            'ord_serv_observaciones'  => $r->ord_serv_observaciones,
            'ord_serv_fecha'          => $r->ord_serv_fecha,
            'ord_serv_fecha_vence'    => $r->ord_serv_fecha_vence,
            'ord_serv_tipo'           => $r->ord_serv_tipo,
            'ord_serv_estado'         => 'PENDIENTE',
            'presupuesto_serv_cab_id' => $r->presupuesto_serv_cab_id,
            'clientes_id'             => $r->clientes_id,
            'empresa_id'              => $r->empresa_id,
            'sucursal_id'             => $r->sucursal_id,
            'diagnostico_cab_id'      => $r->diagnostico_cab_id,
            'recep_cab_id'            => $presupuestoParaRecep->recep_cab_id ?? null,
            'tipo_diagnostico_id'     => $r->tipo_diagnostico_id,
            'tipo_vehiculo_id'        => $r->tipo_vehiculo_id,
            'equipo_trabajo_id'       => $r->equipo_trabajo_id,
            'funcionario_id'          => auth()->user()->funcionario_id,
        ]);

        $detalles = DB::select("
            SELECT psd.item_id,
                   psd.pres_serv_det_costo          AS orden_serv_det_costo,
                   psd.pres_serv_det_cantidad        AS orden_serv_det_cantidad,
                   psd.pres_serv_det_cantidad_stock  AS orden_serv_det_cantidad_stock,
                   psd.tipo_impuesto_id,
                   psd.marca_id,
                   psd.modelo_id
            FROM presupuesto_serv_det psd
            WHERE psd.presupuesto_serv_cab_id = ?
        ", [$r->presupuesto_serv_cab_id]);

        foreach ($detalles as $det) {
            OrdenServDet::create([
                'orden_serv_cab_id'             => $ordenservcab->id,
                'item_id'                       => $det->item_id,
                'orden_serv_det_costo'          => $det->orden_serv_det_costo,
                'orden_serv_det_cantidad'       => $det->orden_serv_det_cantidad,
                'orden_serv_det_cantidad_stock' => $det->orden_serv_det_cantidad_stock,
                'tipo_impuesto_id'              => $det->tipo_impuesto_id,
                'marca_id'                      => $det->marca_id  ?? null,
                'modelo_id'                     => $det->modelo_id ?? null,
            ]);
        }

        DB::table('presupuesto_serv_cab')
            ->where('id', $r->presupuesto_serv_cab_id)
            ->update(['pres_serv_cab_estado' => 'PROCESADO']);

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $ordenservcab,
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $ordenservcab = OrdenServCab::find($id);
        if (!$ordenservcab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($ordenservcab->ord_serv_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se puede modificar una orden de servicio en estado PENDIENTE.', 'tipo' => 'warning'], 409);
        }

        $r->validate([
            'ord_serv_observaciones'  => ['required', 'string', 'max:500', 'not_regex:/[*<>{}|]/'],
            'ord_serv_fecha'          => 'required|date_format:d/m/Y H:i:s',
            'ord_serv_fecha_vence'    => 'required|date_format:d/m/Y H:i:s|after_or_equal:ord_serv_fecha',
            'ord_serv_tipo'           => 'required|string|max:50',
            'presupuesto_serv_cab_id' => 'required|integer|exists:presupuesto_serv_cab,id',
            'clientes_id'             => 'required|integer|exists:clientes,id',
            'empresa_id'              => 'required|integer|exists:empresa,id',
            'sucursal_id'             => 'required|integer|exists:sucursal,id',
            'diagnostico_cab_id'      => 'required|integer|exists:diagnostico_cab,id',
            'tipo_diagnostico_id'     => 'required|integer|exists:tipo_diagnostico,id',
            'tipo_vehiculo_id'        => 'required|integer|exists:tipo_vehiculo,id',
            'equipo_trabajo_id'       => 'required|integer|exists:equipo_trabajo,id',
        ]);

        $ordenservcab->update([
            'ord_serv_observaciones'  => $r->ord_serv_observaciones,
            'ord_serv_fecha'          => $r->ord_serv_fecha,
            'ord_serv_fecha_vence'    => $r->ord_serv_fecha_vence,
            'ord_serv_tipo'           => $r->ord_serv_tipo,
            'presupuesto_serv_cab_id' => $r->presupuesto_serv_cab_id,
            'clientes_id'             => $r->clientes_id,
            'empresa_id'              => $r->empresa_id,
            'sucursal_id'             => $r->sucursal_id,
            'diagnostico_cab_id'      => $r->diagnostico_cab_id,
            'tipo_diagnostico_id'     => $r->tipo_diagnostico_id,
            'tipo_vehiculo_id'        => $r->tipo_vehiculo_id,
            'equipo_trabajo_id'       => $r->equipo_trabajo_id,
        ]);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $ordenservcab,
        ], 200);
    }

    public function anular(Request $r, $id)
    {
        $ordenservcab = OrdenServCab::find($id);
        if (!$ordenservcab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($ordenservcab->ord_serv_estado === 'ANULADO') {
            return response()->json(['mensaje' => 'La orden de servicio ya está anulada.', 'tipo' => 'warning'], 409);
        }

        if ($ordenservcab->ord_serv_estado === 'CONFIRMADO') {
            return response()->json(['mensaje' => 'No se puede anular una orden de servicio CONFIRMADA. Contáctese con el administrador.', 'tipo' => 'warning'], 409);
        }

        $ordenservcab->ord_serv_estado = 'ANULADO';
        $ordenservcab->save();

        DB::table('presupuesto_serv_cab')
            ->where('id', $ordenservcab->presupuesto_serv_cab_id)
            ->update(['pres_serv_cab_estado' => 'CONFIRMADO']);

        return response()->json([
            'mensaje'  => 'Orden de servicio anulada con éxito',
            'tipo'     => 'success',
            'registro' => $ordenservcab,
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $ordenservcab = OrdenServCab::find($id);
        if (!$ordenservcab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($ordenservcab->ord_serv_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se puede confirmar una orden de servicio en estado PENDIENTE.', 'tipo' => 'warning'], 409);
        }

        $ordenservcab->ord_serv_estado = 'CONFIRMADO';
        $ordenservcab->save();

        return response()->json([
            'mensaje'  => 'Orden de servicio confirmada con éxito',
            'tipo'     => 'success',
            'registro' => $ordenservcab,
        ], 200);
    }

    public function buscarParaContrato(Request $r)
    {
        $texto = '%' . ($r->texto ?? '') . '%';
        return DB::select("
            SELECT
                osc.id,
                'ORDEN Nº: ' || TO_CHAR(osc.id, '0000000') ||
                ' (' || COALESCE(osc.ord_serv_observaciones, '') || ')' AS orden_texto,
                c.cli_nombre,
                c.cli_apellido,
                osc.ord_serv_estado,
                osc.ord_serv_fecha,
                osc.ord_serv_observaciones,
                et.equipo_nombre,
                tv.tip_veh_nombre
            FROM orden_serv_cab osc
            JOIN clientes c        ON c.id  = osc.clientes_id
            JOIN equipo_trabajo et ON et.id = osc.equipo_trabajo_id
            JOIN tipo_vehiculo tv  ON tv.id = osc.tipo_vehiculo_id
            WHERE osc.ord_serv_estado IN ('PENDIENTE', 'CONFIRMADO')
            AND (osc.ord_serv_observaciones ILIKE ? OR c.cli_nombre ILIKE ? OR c.cli_apellido ILIKE ?)
            ORDER BY osc.id DESC
            LIMIT 10
        ", [$texto, $texto, $texto]);
    }

    public function buscar(Request $r)
    {
        return DB::select("
            SELECT
                rc.id AS recep_cab_id,
                rc.recep_cab_observaciones,
                rc.recep_cab_estado,
                rc.recep_cab_prioridad,
                rc.recep_cab_kilometraje,
                rc.recep_cab_nivel_combustible,
                rc.funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido AS encargado,
                rc.created_at,
                rc.updated_at,

                c.id AS clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                c.cli_correo,

                ts.id AS tipo_servicio_id,
                ts.tipo_serv_nombre AS tipo_servicio,

                rc.sucursal_id,
                s.suc_razon_social,
                rc.empresa_id,
                e.emp_razon_social,

                'RECEPCION NRO: ' || TO_CHAR(rc.id, '0000000') ||
                ' (' || rc.recep_cab_observaciones || ')' AS recepcion

            FROM recep_cab rc
            JOIN funcionario f  ON f.id  = rc.funcionario_id
            JOIN clientes c     ON c.id  = rc.clientes_id
            JOIN tipo_servicio ts ON ts.id = rc.tipo_servicio_id
            JOIN sucursal s     ON s.id  = rc.sucursal_id
            JOIN empresa e      ON e.id  = rc.empresa_id
            WHERE rc.recep_cab_estado = 'CONFIRMADO'
            AND rc.funcionario_id = ?
            AND (f.fun_nom || ' ' || f.fun_apellido) ILIKE ?
        ", [$r->funcionario_id, '%' . ($r->name ?? '') . '%']);
    }
}
