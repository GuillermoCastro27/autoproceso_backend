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
                TO_CHAR(rc.recep_cab_fecha,           'dd/mm/yyyy HH24:mi:ss') AS recep_cab_fecha,
                TO_CHAR(rc.recep_cab_fecha_estimada,  'dd/mm/yyyy HH24:mi:ss') AS recep_cab_fecha_estimada,
                rc.recep_cab_prioridad,
                rc.recep_cab_estado,
                rc.recep_cab_observaciones,
                rc.recep_cab_kilometraje,
                rc.recep_cab_nivel_combustible,
                rc.recep_cab_num_chasis,
                TO_CHAR(rc.recep_cab_fecha_salida, 'dd/mm/yyyy HH24:mi:ss') AS recep_cab_fecha_salida,

                c.id   AS clientes_id,
                c.cli_nombre, c.cli_apellido, c.cli_ruc,
                c.cli_direccion, c.cli_telefono, c.cli_correo,

                rc.empresa_id,
                e.emp_razon_social,
                rc.sucursal_id,
                s.suc_razon_social,

                sc.id  AS solicitudes_cab_id,
                sc.soli_cab_fecha,
                sc.soli_cab_fecha_estimada,
                sc.soli_cab_prioridad,
                sc.soli_cab_observaciones,
                'SOLICITUD NRO: ' || TO_CHAR(sc.id, '0000000') AS solicitudes,

                ts.id  AS tipo_servicio_id,
                ts.tipo_serv_nombre AS tipo_servicio,

                tv.id  AS tipo_vehiculo_id,
                tv.tip_veh_nombre,
                tv.tip_veh_capacidad,
                tv.tip_veh_combustible,
                tv.tip_veh_categoria,
                tv.tip_veh_observacion,

                m.id   AS marca_id,
                m.marc_nom,
                mo.id  AS modelo_id,
                mo.modelo_nom,
                mo.modelo_año,

                (tv.tip_veh_nombre || ' - ' || m.marc_nom || ' ' || mo.modelo_nom || ' ' || mo.modelo_año) AS vehiculo_info,

                f.fun_nom || ' ' || f.fun_apellido AS encargado

            FROM recep_cab rc
            JOIN funcionario    f  ON f.id  = rc.funcionario_id
            JOIN sucursal       s  ON s.id  = rc.sucursal_id
            JOIN empresa        e  ON e.id  = rc.empresa_id
            JOIN solicitudes_cab sc ON sc.id = rc.solicitudes_cab_id
            JOIN clientes       c  ON c.id  = sc.clientes_id
            JOIN tipo_vehiculo  tv ON tv.id = rc.tipo_vehiculo_id
            JOIN marca          m  ON m.id  = tv.marca_id
            JOIN modelo         mo ON mo.id = tv.modelo_id
            LEFT JOIN tipo_servicio ts ON ts.id = rc.tipo_servicio_id
            ORDER BY rc.id DESC
        ");
    }

    public function store(Request $r)
    {
        $r->validate([
            'recep_cab_observaciones'    => 'required|string|max:500|not_regex:/[*<>{}|]/',
            'recep_cab_fecha'            => 'required|date_format:d/m/Y H:i:s',
            'recep_cab_fecha_estimada'   => 'required|date_format:d/m/Y H:i:s|after_or_equal:recep_cab_fecha',
            'recep_cab_prioridad'        => 'required|in:ALTA,MEDIA,BAJA',
            'recep_cab_kilometraje'      => 'required|numeric|min:0',
            'recep_cab_nivel_combustible'=> 'required|string|max:50',
            'recep_cab_num_chasis'       => ['nullable', 'string', 'max:30', 'not_regex:/[*<>{}|]/'],
            'solicitudes_cab_id'         => 'required|integer|exists:solicitudes_cab,id',
            'clientes_id'                => 'required|integer|exists:clientes,id',
            'tipo_servicio_id'           => 'required|integer|exists:tipo_servicio,id',
            'tipo_vehiculo_id'           => 'required|integer|exists:tipo_vehiculo,id',
            'empresa_id'                 => 'required|integer|exists:empresa,id',
            'sucursal_id'                => 'required|integer|exists:sucursal,id',
        ], [
            'recep_cab_observaciones.required'      => 'Las observaciones son obligatorias.',
            'recep_cab_observaciones.max'           => 'Las observaciones no pueden superar 500 caracteres.',
            'recep_cab_observaciones.not_regex'     => 'Las observaciones contienen caracteres no permitidos.',
            'recep_cab_fecha.required'              => 'La fecha de recepción es obligatoria.',
            'recep_cab_fecha.date_format'           => 'El formato de la fecha de recepción no es válido.',
            'recep_cab_fecha_estimada.required'     => 'La fecha estimada es obligatoria.',
            'recep_cab_fecha_estimada.date_format'  => 'El formato de la fecha estimada no es válido.',
            'recep_cab_fecha_estimada.after_or_equal' => 'La fecha estimada no puede ser anterior a la fecha de recepción.',
            'recep_cab_prioridad.required'          => 'La prioridad es obligatoria.',
            'recep_cab_prioridad.in'                => 'La prioridad debe ser ALTA, MEDIA o BAJA.',
            'recep_cab_kilometraje.required'        => 'El kilometraje es obligatorio.',
            'recep_cab_kilometraje.numeric'         => 'El kilometraje debe ser un número.',
            'recep_cab_kilometraje.min'             => 'El kilometraje no puede ser negativo.',
            'recep_cab_nivel_combustible.required'  => 'El nivel de combustible es obligatorio.',
            'recep_cab_num_chasis.not_regex'        => 'El número de chasis contiene caracteres no permitidos.',
            'solicitudes_cab_id.required'           => 'Debe seleccionar una solicitud.',
            'solicitudes_cab_id.exists'             => 'La solicitud seleccionada no es válida.',
            'clientes_id.required'                  => 'Debe seleccionar un cliente.',
            'tipo_servicio_id.required'             => 'Debe seleccionar un tipo de servicio.',
            'tipo_vehiculo_id.required'             => 'Debe seleccionar el tipo de vehículo.',
            'tipo_vehiculo_id.exists'               => 'El tipo de vehículo seleccionado no es válido.',
        ]);

        $recepcion = RecepcionCab::create([
            'recep_cab_observaciones'    => $r->recep_cab_observaciones,
            'recep_cab_fecha'            => $r->recep_cab_fecha,
            'recep_cab_fecha_estimada'   => $r->recep_cab_fecha_estimada,
            'recep_cab_prioridad'        => $r->recep_cab_prioridad,
            'recep_cab_kilometraje'      => $r->recep_cab_kilometraje,
            'recep_cab_nivel_combustible'=> $r->recep_cab_nivel_combustible,
            'recep_cab_num_chasis'       => $r->recep_cab_num_chasis ?? null,
            'recep_cab_estado'           => 'PENDIENTE',
            'solicitudes_cab_id'         => $r->solicitudes_cab_id,
            'clientes_id'                => $r->clientes_id,
            'tipo_servicio_id'           => $r->tipo_servicio_id,
            'tipo_vehiculo_id'           => $r->tipo_vehiculo_id,
            'empresa_id'                 => $r->empresa_id,
            'sucursal_id'                => $r->sucursal_id,
            'funcionario_id'             => auth()->user()->funcionario_id,
        ]);

        // Marcar solicitud como PROCESADO
        $solicitud = SolicitudCab::find($r->solicitudes_cab_id);
        if ($solicitud) {
            $solicitud->soli_cab_estado = 'PROCESADO';
            $solicitud->save();
        }

        // Copiar detalles de la solicitud (incluyendo marca/modelo)
        $detalles = DB::select("
            SELECT sd.item_id, sd.soli_det_costo, sd.soli_det_cantidad,
                   sd.soli_det_cantidad_stock, sd.tipo_impuesto_id,
                   sd.marca_id, sd.modelo_id
            FROM solicitudes_det sd
            WHERE sd.solicitudes_cab_id = ?
        ", [$r->solicitudes_cab_id]);

        foreach ($detalles as $sd) {
            $det = new RecepcionDet();
            $det->recep_cab_id              = $recepcion->id;
            $det->item_id                   = $sd->item_id;
            $det->recep_det_costo           = $sd->soli_det_costo;
            $det->recep_det_cantidad        = $sd->soli_det_cantidad;
            $det->recep_det_cantidad_stock  = $sd->soli_det_cantidad_stock;
            $det->tipo_impuesto_id          = $sd->tipo_impuesto_id;
            $det->marca_id                  = $sd->marca_id  ?? null;
            $det->modelo_id                 = $sd->modelo_id ?? null;
            $det->save();
        }

        return response()->json([
            'mensaje'  => 'Recepción creada con éxito',
            'tipo'     => 'success',
            'registro' => $recepcion,
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $recepcion = RecepcionCab::find($id);
        if (!$recepcion) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }
        if ($recepcion->recep_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden editar recepciones en estado PENDIENTE.', 'tipo' => 'warning'], 400);
        }

        $r->validate([
            'recep_cab_observaciones'    => 'required|string|max:500|not_regex:/[*<>{}|]/',
            'recep_cab_fecha'            => 'required|date_format:d/m/Y H:i:s',
            'recep_cab_fecha_estimada'   => 'required|date_format:d/m/Y H:i:s|after_or_equal:recep_cab_fecha',
            'recep_cab_prioridad'        => 'required|in:ALTA,MEDIA,BAJA',
            'recep_cab_kilometraje'      => 'required|numeric|min:0',
            'recep_cab_nivel_combustible'=> 'required|string|max:50',
            'recep_cab_num_chasis'       => ['nullable', 'string', 'max:30', 'not_regex:/[*<>{}|]/'],
            'solicitudes_cab_id'         => 'required|integer|exists:solicitudes_cab,id',
            'clientes_id'                => 'required|integer|exists:clientes,id',
            'tipo_servicio_id'           => 'required|integer|exists:tipo_servicio,id',
            'tipo_vehiculo_id'           => 'required|integer|exists:tipo_vehiculo,id',
            'empresa_id'                 => 'required|integer|exists:empresa,id',
            'sucursal_id'                => 'required|integer|exists:sucursal,id',
        ]);

        $recepcion->update([
            'recep_cab_observaciones'    => $r->recep_cab_observaciones,
            'recep_cab_fecha'            => $r->recep_cab_fecha,
            'recep_cab_fecha_estimada'   => $r->recep_cab_fecha_estimada,
            'recep_cab_prioridad'        => $r->recep_cab_prioridad,
            'recep_cab_kilometraje'      => $r->recep_cab_kilometraje,
            'recep_cab_nivel_combustible'=> $r->recep_cab_nivel_combustible,
            'recep_cab_num_chasis'       => $r->recep_cab_num_chasis ?? null,
            'solicitudes_cab_id'         => $r->solicitudes_cab_id,
            'clientes_id'                => $r->clientes_id,
            'tipo_servicio_id'           => $r->tipo_servicio_id,
            'tipo_vehiculo_id'           => $r->tipo_vehiculo_id,
            'empresa_id'                 => $r->empresa_id,
            'sucursal_id'                => $r->sucursal_id,
        ]);

        return response()->json([
            'mensaje'  => 'Recepción modificada con éxito',
            'tipo'     => 'success',
            'registro' => $recepcion,
        ], 200);
    }

    public function anular(Request $r, $id)
    {
        $recepcion = RecepcionCab::find($id);
        if (!$recepcion) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }
        if ($recepcion->recep_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden anular recepciones en estado PENDIENTE.', 'tipo' => 'warning'], 400);
        }

        $recepcion->update(['recep_cab_estado' => 'ANULADO']);

        // Revertir la solicitud a CONFIRMADO
        $solicitud = SolicitudCab::find($recepcion->solicitudes_cab_id);
        if ($solicitud) {
            $solicitud->soli_cab_estado = 'CONFIRMADO';
            $solicitud->save();
        }

        return response()->json([
            'mensaje'  => 'Recepción anulada con éxito',
            'tipo'     => 'success',
            'registro' => $recepcion,
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $recepcion = RecepcionCab::find($id);
        if (!$recepcion) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }
        if ($recepcion->recep_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden confirmar recepciones en estado PENDIENTE.', 'tipo' => 'warning'], 400);
        }

        $recepcion->update(['recep_cab_estado' => 'CONFIRMADO']);

        return response()->json([
            'mensaje'  => 'Recepción confirmada con éxito',
            'tipo'     => 'success',
            'registro' => $recepcion,
        ], 200);
    }

    public function registrarSalida($id)
    {
        $recepcion = RecepcionCab::find($id);
        if (!$recepcion) {
            return response()->json(['mensaje' => 'Recepción no encontrada.', 'tipo' => 'error'], 404);
        }

        if ($recepcion->recep_cab_fecha_salida) {
            return response()->json(['mensaje' => 'Esta recepción ya tiene registrada su salida.', 'tipo' => 'warning'], 400);
        }

        // Validar que exista una Orden de Servicio en estado TERMINADO ligada a esta recepción
        $ordenTerminada = DB::selectOne("
            SELECT id FROM orden_serv_cab
            WHERE recep_cab_id = ?
              AND LOWER(ord_serv_estado) = 'terminado'
            LIMIT 1
        ", [$id]);

        if (!$ordenTerminada) {
            return response()->json([
                'mensaje' => 'No se puede registrar la salida. La orden de servicio aún no está terminada (cobro pendiente).',
                'tipo'    => 'warning'
            ], 400);
        }

        $recepcion->update(['recep_cab_fecha_salida' => now()]);

        return response()->json([
            'mensaje'      => 'Salida del vehículo registrada correctamente.',
            'tipo'         => 'success',
            'fecha_salida' => $recepcion->recep_cab_fecha_salida,
        ], 200);
    }

    private function datosTicket($id)
    {
        $cab = DB::selectOne("
            SELECT
                rc.id,
                TO_CHAR(rc.recep_cab_fecha,          'DD/MM/YYYY HH24:MI:SS') AS recep_cab_fecha,
                TO_CHAR(rc.recep_cab_fecha_estimada, 'DD/MM/YYYY HH24:MI:SS') AS recep_cab_fecha_estimada,
                rc.recep_cab_prioridad,
                rc.recep_cab_estado,
                rc.recep_cab_observaciones,
                rc.recep_cab_kilometraje,
                rc.recep_cab_nivel_combustible,
                rc.recep_cab_num_chasis,
                c.cli_nombre, c.cli_apellido, c.cli_ruc,
                c.cli_telefono, c.cli_correo, c.cli_direccion,
                e.emp_razon_social,
                COALESCE(e.emp_direccion, '') AS emp_direccion,
                COALESCE(e.emp_telefono,  '') AS emp_telefono,
                s.suc_razon_social,
                COALESCE(ts.tipo_serv_nombre, '') AS tipo_serv_nombre,
                tv.tip_veh_nombre,
                m.marc_nom,
                mo.modelo_nom,
                COALESCE(mo.modelo_año::varchar, '') AS modelo_año,
                (tv.tip_veh_nombre || ' - ' || m.marc_nom || ' ' || mo.modelo_nom || ' ' || COALESCE(mo.modelo_año::varchar, '')) AS vehiculo_info,
                f.fun_nom || ' ' || f.fun_apellido AS encargado
            FROM recep_cab rc
            JOIN funcionario    f  ON f.id  = rc.funcionario_id
            JOIN sucursal       s  ON s.id  = rc.sucursal_id
            JOIN empresa        e  ON e.id  = rc.empresa_id
            JOIN clientes       c  ON c.id  = rc.clientes_id
            JOIN tipo_vehiculo  tv ON tv.id = rc.tipo_vehiculo_id
            JOIN marca          m  ON m.id  = tv.marca_id
            JOIN modelo         mo ON mo.id = tv.modelo_id
            LEFT JOIN tipo_servicio ts ON ts.id = rc.tipo_servicio_id
            WHERE rc.id = ?
        ", [$id]);

        if (!$cab) return null;

        $detalles = DB::select("
            SELECT
                rd.recep_det_cantidad AS cantidad,
                rd.recep_det_costo    AS precio,
                i.item_decripcion,
                ti.tip_imp_nom,
                COALESCE(ma.marc_nom, '') AS marc_nom,
                COALESCE(mo.modelo_nom,'') AS modelo_nom
            FROM recep_det rd
            JOIN items         i  ON i.id  = rd.item_id
            JOIN tipo_impuesto ti ON ti.id = rd.tipo_impuesto_id
            LEFT JOIN marca    ma ON ma.id = rd.marca_id
            LEFT JOIN modelo   mo ON mo.id = rd.modelo_id
            WHERE rd.recep_cab_id = ?
        ", [$id]);

        return compact('cab', 'detalles');
    }

    public function imprimir($id)
    {
        $data = $this->datosTicket($id);
        if (!$data) {
            return response()->json(['mensaje' => 'Recepción no encontrada', 'tipo' => 'error'], 404);
        }
        return response()->json(['cab' => $data['cab'], 'detalles' => $data['detalles']]);
    }

    public function enviarTicket($id)
    {
        $data = $this->datosTicket($id);
        if (!$data) {
            return response()->json(['mensaje' => 'Recepción no encontrada', 'tipo' => 'error'], 404);
        }

        $cab = $data['cab'];
        if (empty($cab->cli_correo)) {
            return response()->json(['mensaje' => 'El cliente no tiene correo registrado', 'tipo' => 'warning']);
        }

        $datos = array_merge((array) $cab, ['detalles' => $data['detalles']]);
        \Mail::to($cab->cli_correo)->send(new \App\Mail\TicketRecepcion($datos));

        return response()->json([
            'mensaje' => 'Ticket enviado correctamente a ' . $cab->cli_correo,
            'tipo'    => 'success',
        ]);
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

                c.id AS clientes_id,
                c.cli_nombre, c.cli_apellido, c.cli_ruc,
                c.cli_direccion, c.cli_telefono, c.cli_correo,

                ts.id AS tipo_servicio_id,
                ts.tipo_serv_nombre AS tipo_servicio,

                rc.sucursal_id, s.suc_razon_social,
                rc.empresa_id,  e.emp_razon_social,

                tv.id AS tipo_vehiculo_id,
                tv.tip_veh_nombre, tv.tip_veh_capacidad,
                tv.tip_veh_combustible, tv.tip_veh_categoria, tv.tip_veh_observacion,

                m.id  AS marca_id,  m.marc_nom,
                mo.id AS modelo_id, mo.modelo_nom, mo.modelo_año,

                (tv.tip_veh_nombre || ' - ' || m.marc_nom || ' ' || mo.modelo_nom || ' ' || mo.modelo_año) AS vehiculo_info,

                'RECEPCION NRO: ' || TO_CHAR(rc.id, '0000000') ||
                ' (' || rc.recep_cab_observaciones || ')' AS recepcion

            FROM recep_cab rc
            JOIN funcionario   f  ON f.id  = rc.funcionario_id
            JOIN clientes      c  ON c.id  = rc.clientes_id
            JOIN tipo_servicio ts ON ts.id = rc.tipo_servicio_id
            JOIN sucursal      s  ON s.id  = rc.sucursal_id
            JOIN empresa       e  ON e.id  = rc.empresa_id
            JOIN tipo_vehiculo tv ON tv.id = rc.tipo_vehiculo_id
            JOIN marca         m  ON m.id  = tv.marca_id
            JOIN modelo        mo ON mo.id = tv.modelo_id
            WHERE rc.recep_cab_estado = 'CONFIRMADO'
              AND rc.funcionario_id  = ?
              AND (f.fun_nom || ' ' || f.fun_apellido) ILIKE ?
        ", [$r->funcionario_id, '%' . $r->name . '%']);
    }
}
