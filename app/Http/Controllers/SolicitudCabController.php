<?php

namespace App\Http\Controllers;
use App\Models\SolicitudCab;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SolicitudCabController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                sc.id,
                TO_CHAR(sc.soli_cab_fecha, 'dd/mm/yyyy HH24:mi:ss')          AS soli_cab_fecha,
                TO_CHAR(sc.soli_cab_fecha_estimada, 'dd/mm/yyyy HH24:mi:ss') AS soli_cab_fecha_estimada,
                sc.soli_cab_observaciones,
                sc.soli_cab_prioridad,
                sc.soli_cab_estado,

                sc.sucursal_id,
                s.suc_razon_social,

                sc.empresa_id,
                e.emp_razon_social,

                sc.clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                c.cli_correo,

                sc.tipo_servicio_id,
                ts.tipo_serv_nombre,

                sc.funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,

                sc.created_at,
                sc.updated_at
            FROM solicitudes_cab sc
            JOIN sucursal     s  ON s.id  = sc.sucursal_id
            JOIN empresa      e  ON e.id  = sc.empresa_id
            JOIN clientes     c  ON c.id  = sc.clientes_id
            JOIN tipo_servicio ts ON ts.id = sc.tipo_servicio_id
            JOIN funcionario  f  ON f.id  = sc.funcionario_id
            ORDER BY sc.id DESC
        ");
    }

    public function store(Request $r)
    {
        $r->validate([
            'soli_cab_observaciones'  => ['required', 'string', 'max:500', 'not_regex:/[*<>{}|]/'],
            'soli_cab_fecha'          => 'required|date_format:d/m/Y H:i:s',
            'soli_cab_fecha_estimada' => 'required|date_format:d/m/Y H:i:s|after_or_equal:soli_cab_fecha',
            'soli_cab_prioridad'      => 'required|in:ALTA,MEDIA,BAJA',
            'clientes_id'             => 'required|integer|exists:clientes,id',
            'tipo_servicio_id'        => 'required|integer|exists:tipo_servicio,id',
            'empresa_id'              => 'required|integer|exists:empresa,id',
            'sucursal_id'             => 'required|integer|exists:sucursal,id',
        ], [
            'soli_cab_observaciones.required'      => 'Las observaciones son obligatorias.',
            'soli_cab_observaciones.max'           => 'Las observaciones no pueden superar 500 caracteres.',
            'soli_cab_observaciones.not_regex'     => 'Las observaciones contienen caracteres no permitidos.',
            'soli_cab_fecha.required'              => 'La fecha de solicitud es obligatoria.',
            'soli_cab_fecha.date_format'           => 'El formato de la fecha de solicitud no es válido.',
            'soli_cab_fecha_estimada.required'     => 'La fecha estimada es obligatoria.',
            'soli_cab_fecha_estimada.date_format'  => 'El formato de la fecha estimada no es válido.',
            'soli_cab_fecha_estimada.after_or_equal' => 'La fecha estimada no puede ser anterior a la fecha de solicitud.',
            'soli_cab_prioridad.required'          => 'La prioridad es obligatoria.',
            'soli_cab_prioridad.in'                => 'La prioridad debe ser ALTA, MEDIA o BAJA.',
            'clientes_id.required'                 => 'Debe seleccionar un cliente.',
            'clientes_id.exists'                   => 'El cliente seleccionado no es válido.',
            'tipo_servicio_id.required'            => 'Debe seleccionar un tipo de servicio.',
            'tipo_servicio_id.exists'              => 'El tipo de servicio seleccionado no es válido.',
            'empresa_id.required'                  => 'La empresa es obligatoria.',
            'sucursal_id.required'                 => 'La sucursal es obligatoria.',
        ]);

        $solicitud = SolicitudCab::create([
            'soli_cab_observaciones'  => $r->soli_cab_observaciones,
            'soli_cab_fecha'          => $r->soli_cab_fecha,
            'soli_cab_fecha_estimada' => $r->soli_cab_fecha_estimada,
            'soli_cab_prioridad'      => $r->soli_cab_prioridad,
            'soli_cab_estado'         => 'PENDIENTE',
            'clientes_id'             => $r->clientes_id,
            'tipo_servicio_id'        => $r->tipo_servicio_id,
            'empresa_id'              => $r->empresa_id,
            'sucursal_id'             => $r->sucursal_id,
            'funcionario_id'          => auth()->user()->funcionario_id,
        ]);

        return response()->json([
            'mensaje'  => 'Solicitud creada con éxito',
            'tipo'     => 'success',
            'registro' => $solicitud,
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $solicitud = SolicitudCab::find($id);
        if (!$solicitud) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }
        if ($solicitud->soli_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden editar solicitudes en estado PENDIENTE.', 'tipo' => 'warning'], 400);
        }

        $r->validate([
            'soli_cab_observaciones'  => ['required', 'string', 'max:500', 'not_regex:/[*<>{}|]/'],
            'soli_cab_fecha'          => 'required|date_format:d/m/Y H:i:s',
            'soli_cab_fecha_estimada' => 'required|date_format:d/m/Y H:i:s|after_or_equal:soli_cab_fecha',
            'soli_cab_prioridad'      => 'required|in:ALTA,MEDIA,BAJA',
            'clientes_id'             => 'required|integer|exists:clientes,id',
            'tipo_servicio_id'        => 'required|integer|exists:tipo_servicio,id',
            'empresa_id'              => 'required|integer|exists:empresa,id',
            'sucursal_id'             => 'required|integer|exists:sucursal,id',
        ], [
            'soli_cab_observaciones.required'        => 'Las observaciones son obligatorias.',
            'soli_cab_observaciones.max'             => 'Las observaciones no pueden superar 500 caracteres.',
            'soli_cab_observaciones.not_regex'       => 'Las observaciones contienen caracteres no permitidos.',
            'soli_cab_fecha.required'                => 'La fecha de solicitud es obligatoria.',
            'soli_cab_fecha.date_format'             => 'El formato de la fecha de solicitud no es válido.',
            'soli_cab_fecha_estimada.required'       => 'La fecha estimada es obligatoria.',
            'soli_cab_fecha_estimada.date_format'    => 'El formato de la fecha estimada no es válido.',
            'soli_cab_fecha_estimada.after_or_equal' => 'La fecha estimada no puede ser anterior a la fecha de solicitud.',
            'soli_cab_prioridad.required'            => 'La prioridad es obligatoria.',
            'soli_cab_prioridad.in'                  => 'La prioridad debe ser ALTA, MEDIA o BAJA.',
            'clientes_id.required'                   => 'Debe seleccionar un cliente.',
            'clientes_id.exists'                     => 'El cliente seleccionado no es válido.',
            'tipo_servicio_id.required'              => 'Debe seleccionar un tipo de servicio.',
            'tipo_servicio_id.exists'                => 'El tipo de servicio seleccionado no es válido.',
        ]);

        $solicitud->update([
            'soli_cab_observaciones'  => $r->soli_cab_observaciones,
            'soli_cab_fecha'          => $r->soli_cab_fecha,
            'soli_cab_fecha_estimada' => $r->soli_cab_fecha_estimada,
            'soli_cab_prioridad'      => $r->soli_cab_prioridad,
            'clientes_id'             => $r->clientes_id,
            'tipo_servicio_id'        => $r->tipo_servicio_id,
            'empresa_id'              => $r->empresa_id,
            'sucursal_id'             => $r->sucursal_id,
        ]);

        return response()->json([
            'mensaje'  => 'Solicitud modificada con éxito',
            'tipo'     => 'success',
            'registro' => $solicitud,
        ], 200);
    }

    public function anular(Request $r, $id)
    {
        $solicitud = SolicitudCab::find($id);
        if (!$solicitud) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }
        if ($solicitud->soli_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden anular solicitudes en estado PENDIENTE.', 'tipo' => 'warning'], 400);
        }

        $solicitud->update(['soli_cab_estado' => 'ANULADO']);

        return response()->json([
            'mensaje'  => 'Solicitud anulada con éxito',
            'tipo'     => 'success',
            'registro' => $solicitud,
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $solicitud = SolicitudCab::find($id);
        if (!$solicitud) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }
        if ($solicitud->soli_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden confirmar solicitudes en estado PENDIENTE.', 'tipo' => 'warning'], 400);
        }

        $solicitud->update(['soli_cab_estado' => 'CONFIRMADO']);

        return response()->json([
            'mensaje'  => 'Solicitud confirmada con éxito',
            'tipo'     => 'success',
            'registro' => $solicitud,
        ], 200);
    }

    public function buscar(Request $r)
    {
        return DB::select("
            SELECT
                sc.id AS solicitudes_cab_id,
                TO_CHAR(sc.soli_cab_fecha_estimada, 'dd/mm/yyyy HH24:mi:ss') AS soli_cab_fecha_estimada,
                sc.soli_cab_observaciones,
                sc.soli_cab_estado,
                sc.soli_cab_prioridad,
                sc.funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido AS encargado,

                c.id AS clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                c.cli_correo,

                ts.id AS tipo_servicio_id,
                ts.tipo_serv_nombre AS tipo_servicio,

                sc.sucursal_id,
                s.suc_razon_social,
                sc.empresa_id,
                e.emp_razon_social,

                'SOLICITUD NRO: ' || TO_CHAR(sc.id, '0000000') ||
                ' (' || sc.soli_cab_observaciones || ')' AS solicitud

            FROM solicitudes_cab sc
            JOIN funcionario   f  ON f.id  = sc.funcionario_id
            JOIN clientes      c  ON c.id  = sc.clientes_id
            JOIN tipo_servicio ts ON ts.id = sc.tipo_servicio_id
            JOIN sucursal      s  ON s.id  = sc.sucursal_id
            JOIN empresa       e  ON e.id  = sc.empresa_id
            WHERE sc.soli_cab_estado = 'CONFIRMADO'
              AND sc.funcionario_id  = ?
              AND (f.fun_nom || ' ' || f.fun_apellido) ILIKE ?
        ", [$r->funcionario_id, '%' . $r->name . '%']);
    }

    public function buscarInforme(Request $request)
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        return DB::select("
            SELECT
                sc.id,
                TO_CHAR(sc.soli_cab_fecha, 'dd/mm/yyyy')          AS fecha,
                TO_CHAR(sc.soli_cab_fecha_estimada, 'dd/mm/yyyy') AS entrega,
                sc.soli_cab_observaciones                          AS observaciones,
                sc.soli_cab_prioridad                              AS prioridad,
                sc.soli_cab_estado                                 AS estado,
                f.fun_nom || ' ' || f.fun_apellido                 AS funcionario,
                s.suc_razon_social                                 AS sucursal,
                e.emp_razon_social                                 AS empresa,
                c.cli_nombre || ' ' || c.cli_apellido             AS cliente
            FROM solicitudes_cab sc
            JOIN funcionario   f  ON f.id  = sc.funcionario_id
            JOIN sucursal      s  ON s.id  = sc.sucursal_id
            JOIN empresa       e  ON e.id  = sc.empresa_id
            JOIN clientes      c  ON c.id  = sc.clientes_id
            WHERE sc.soli_cab_fecha::date BETWEEN ? AND ?
            ORDER BY sc.id DESC
        ", [$desde, $hasta]);
    }
}
