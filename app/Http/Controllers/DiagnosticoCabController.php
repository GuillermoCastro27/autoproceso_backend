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
                TO_CHAR(dc.diag_cab_fecha, 'dd/mm/yyyy HH24:mi:ss') AS diag_cab_fecha,
                dc.diag_cab_prioridad,
                dc.diag_cab_estado,
                dc.diag_cab_observaciones,
                dc.diag_cab_kilometraje,
                dc.diag_cab_nivel_combustible,

                c.id   AS clientes_id,
                c.cli_nombre, c.cli_apellido,
                c.cli_ruc, c.cli_direccion, c.cli_telefono, c.cli_correo,

                rc.sucursal_id,
                s.suc_razon_social,
                rc.empresa_id,
                e.emp_razon_social,

                rc.id AS recep_cab_id,
                TO_CHAR(rc.recep_cab_fecha, 'dd/mm/yyyy HH24:mi:ss') AS recep_cab_fecha,
                rc.recep_cab_prioridad,
                rc.recep_cab_observaciones,
                'RECEPCION NRO: ' || TO_CHAR(rc.id, '0000000') AS recepcion,

                td.id AS tipo_diagnostico_id,
                td.tipo_diag_nombre,

                tv.id AS tipo_vehiculo_id,
                tv.tip_veh_nombre,
                tv.tip_veh_capacidad,
                tv.tip_veh_combustible,
                tv.tip_veh_categoria,
                tv.tip_veh_observacion,

                m.marc_nom  AS marca_nombre,
                mo.modelo_nom AS modelo_nombre,
                mo.modelo_año,

                ts.id AS tipo_servicio_id,
                ts.tipo_serv_nombre,

                f.fun_nom || ' ' || f.fun_apellido AS encargado

            FROM diagnostico_cab dc
            JOIN funcionario   f  ON f.id  = dc.funcionario_id
            JOIN sucursal      s  ON s.id  = dc.sucursal_id
            JOIN empresa       e  ON e.id  = dc.empresa_id
            JOIN recep_cab     rc ON rc.id = dc.recep_cab_id
            JOIN clientes      c  ON c.id  = rc.clientes_id
            LEFT JOIN tipo_diagnostico td ON td.id = dc.tipo_diagnostico_id
            LEFT JOIN tipo_servicio    ts ON ts.id = rc.tipo_servicio_id
            LEFT JOIN tipo_vehiculo    tv ON tv.id = rc.tipo_vehiculo_id
            LEFT JOIN marca            m  ON m.id  = tv.marca_id
            LEFT JOIN modelo           mo ON mo.id = tv.modelo_id
            ORDER BY dc.id DESC
        ");
    }

    public function store(Request $r)
    {
        $r->validate([
            'diag_cab_observaciones'    => 'required|string|max:500|not_regex:/[*<>{}|]/',
            'diag_cab_fecha'            => 'required|date_format:d/m/Y H:i:s',
            'diag_cab_prioridad'        => 'required|in:ALTA,MEDIA,BAJA',
            'diag_cab_kilometraje'      => 'required|numeric|min:0',
            'diag_cab_nivel_combustible'=> 'required|string|max:50',
            'recep_cab_id'              => 'required|integer|exists:recep_cab,id',
            'clientes_id'               => 'required|integer|exists:clientes,id',
            'tipo_diagnostico_id'       => 'required|integer|exists:tipo_diagnostico,id',
            'tipo_vehiculo_id'          => 'required|integer|exists:tipo_vehiculo,id',
            'tipo_servicio_id'          => 'required|integer|exists:tipo_servicio,id',
            'empresa_id'                => 'required|integer|exists:empresa,id',
            'sucursal_id'               => 'required|integer|exists:sucursal,id',
        ], [
            'diag_cab_observaciones.required'      => 'Las observaciones son obligatorias.',
            'diag_cab_observaciones.max'           => 'Las observaciones no pueden superar 500 caracteres.',
            'diag_cab_observaciones.not_regex'     => 'Las observaciones contienen caracteres no permitidos.',
            'diag_cab_fecha.required'              => 'La fecha del diagnóstico es obligatoria.',
            'diag_cab_fecha.date_format'           => 'El formato de la fecha no es válido.',
            'diag_cab_prioridad.required'          => 'La prioridad es obligatoria.',
            'diag_cab_prioridad.in'                => 'La prioridad debe ser ALTA, MEDIA o BAJA.',
            'diag_cab_kilometraje.required'        => 'El kilometraje es obligatorio.',
            'diag_cab_kilometraje.numeric'         => 'El kilometraje debe ser un número.',
            'diag_cab_kilometraje.min'             => 'El kilometraje no puede ser negativo.',
            'diag_cab_nivel_combustible.required'  => 'El nivel de combustible es obligatorio.',
            'recep_cab_id.required'                => 'Debe seleccionar una recepción.',
            'recep_cab_id.exists'                  => 'La recepción seleccionada no es válida.',
            'clientes_id.required'                 => 'Debe seleccionar un cliente.',
            'tipo_diagnostico_id.required'         => 'Debe seleccionar el tipo de diagnóstico.',
            'tipo_diagnostico_id.exists'           => 'El tipo de diagnóstico seleccionado no es válido.',
            'tipo_vehiculo_id.required'            => 'Debe seleccionar el tipo de vehículo.',
            'tipo_servicio_id.required'            => 'Debe seleccionar el tipo de servicio.',
        ]);

        $diagnostico = DiagnosticoCab::create([
            'diag_cab_observaciones'    => $r->diag_cab_observaciones,
            'diag_cab_fecha'            => $r->diag_cab_fecha,
            'diag_cab_prioridad'        => $r->diag_cab_prioridad,
            'diag_cab_kilometraje'      => $r->diag_cab_kilometraje,
            'diag_cab_nivel_combustible'=> $r->diag_cab_nivel_combustible,
            'diag_cab_estado'           => 'PENDIENTE',
            'recep_cab_id'              => $r->recep_cab_id,
            'clientes_id'               => $r->clientes_id,
            'tipo_diagnostico_id'       => $r->tipo_diagnostico_id,
            'tipo_vehiculo_id'          => $r->tipo_vehiculo_id,
            'tipo_servicio_id'          => $r->tipo_servicio_id,
            'empresa_id'                => $r->empresa_id,
            'sucursal_id'               => $r->sucursal_id,
            'funcionario_id'            => auth()->user()->funcionario_id,
        ]);

        // Marcar recepción como PROCESADO
        $recepcion = RecepcionCab::find($r->recep_cab_id);
        if (!$recepcion) {
            return response()->json(['mensaje' => 'Recepción no encontrada', 'tipo' => 'error'], 404);
        }
        $recepcion->recep_cab_estado = 'PROCESADO';
        $recepcion->save();

        // Copiar detalles de recep_det (incluyendo marca/modelo)
        $detalles = DB::select("
            SELECT rd.item_id, rd.recep_det_costo, rd.recep_det_cantidad,
                   rd.recep_det_cantidad_stock, rd.tipo_impuesto_id,
                   rd.marca_id, rd.modelo_id
            FROM recep_det rd
            WHERE rd.recep_cab_id = ?
        ", [$r->recep_cab_id]);

        foreach ($detalles as $dd) {
            $det = new DiagnosticoDet();
            $det->diagnostico_cab_id        = $diagnostico->id;
            $det->item_id                   = $dd->item_id;
            $det->diag_det_costo            = $dd->recep_det_costo;
            $det->diag_det_cantidad         = $dd->recep_det_cantidad;
            $det->diag_det_cantidad_stock   = $dd->recep_det_cantidad_stock;
            $det->tipo_impuesto_id          = $dd->tipo_impuesto_id;
            $det->marca_id                  = $dd->marca_id  ?? null;
            $det->modelo_id                 = $dd->modelo_id ?? null;
            $det->save();
        }

        return response()->json([
            'mensaje'  => 'Diagnóstico creado con éxito',
            'tipo'     => 'success',
            'registro' => $diagnostico,
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $diagnostico = DiagnosticoCab::find($id);
        if (!$diagnostico) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }
        if ($diagnostico->diag_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden editar diagnósticos en estado PENDIENTE.', 'tipo' => 'warning'], 400);
        }

        $r->validate([
            'diag_cab_observaciones'    => 'required|string|max:500|not_regex:/[*<>{}|]/',
            'diag_cab_fecha'            => 'required|date_format:d/m/Y H:i:s',
            'diag_cab_prioridad'        => 'required|in:ALTA,MEDIA,BAJA',
            'diag_cab_kilometraje'      => 'required|numeric|min:0',
            'diag_cab_nivel_combustible'=> 'required|string|max:50',
            'recep_cab_id'              => 'required|integer|exists:recep_cab,id',
            'clientes_id'               => 'required|integer|exists:clientes,id',
            'tipo_diagnostico_id'       => 'required|integer|exists:tipo_diagnostico,id',
            'tipo_vehiculo_id'          => 'required|integer|exists:tipo_vehiculo,id',
            'tipo_servicio_id'          => 'required|integer|exists:tipo_servicio,id',
            'empresa_id'                => 'required|integer|exists:empresa,id',
            'sucursal_id'               => 'required|integer|exists:sucursal,id',
        ]);

        $diagnostico->update([
            'diag_cab_observaciones'    => $r->diag_cab_observaciones,
            'diag_cab_fecha'            => $r->diag_cab_fecha,
            'diag_cab_prioridad'        => $r->diag_cab_prioridad,
            'diag_cab_kilometraje'      => $r->diag_cab_kilometraje,
            'diag_cab_nivel_combustible'=> $r->diag_cab_nivel_combustible,
            'recep_cab_id'              => $r->recep_cab_id,
            'clientes_id'               => $r->clientes_id,
            'tipo_diagnostico_id'       => $r->tipo_diagnostico_id,
            'tipo_vehiculo_id'          => $r->tipo_vehiculo_id,
            'tipo_servicio_id'          => $r->tipo_servicio_id,
            'empresa_id'                => $r->empresa_id,
            'sucursal_id'               => $r->sucursal_id,
        ]);

        return response()->json([
            'mensaje'  => 'Diagnóstico modificado con éxito',
            'tipo'     => 'success',
            'registro' => $diagnostico,
        ], 200);
    }

    public function anular(Request $r, $id)
    {
        $diagnostico = DiagnosticoCab::find($id);
        if (!$diagnostico) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }
        if ($diagnostico->diag_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden anular diagnósticos en estado PENDIENTE.', 'tipo' => 'warning'], 400);
        }

        $diagnostico->update(['diag_cab_estado' => 'ANULADO']);

        // Revertir recepción a CONFIRMADO
        $recepcion = RecepcionCab::find($diagnostico->recep_cab_id);
        if ($recepcion) {
            $recepcion->recep_cab_estado = 'CONFIRMADO';
            $recepcion->save();
        }

        return response()->json([
            'mensaje'  => 'Diagnóstico anulado con éxito',
            'tipo'     => 'success',
            'registro' => $diagnostico,
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $diagnostico = DiagnosticoCab::find($id);
        if (!$diagnostico) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }
        if ($diagnostico->diag_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden confirmar diagnósticos en estado PENDIENTE.', 'tipo' => 'warning'], 400);
        }

        $diagnostico->update(['diag_cab_estado' => 'CONFIRMADO']);

        return response()->json([
            'mensaje'  => 'Diagnóstico confirmado con éxito',
            'tipo'     => 'success',
            'registro' => $diagnostico,
        ], 200);
    }

    public function buscar(Request $r)
    {
        return DB::select("
            SELECT
                dc.id AS diagnostico_cab_id,
                TO_CHAR(dc.diag_cab_fecha, 'DD/MM/YYYY HH24:MI:SS') AS diag_cab_fecha,
                dc.diag_cab_prioridad,
                dc.diag_cab_estado,
                dc.diag_cab_observaciones,
                dc.diag_cab_kilometraje,
                dc.diag_cab_nivel_combustible,

                c.id AS clientes_id,
                c.cli_nombre, c.cli_apellido, c.cli_ruc,
                c.cli_direccion, c.cli_telefono, c.cli_correo,

                td.id AS tipo_diagnostico_id,
                td.tipo_diag_nombre,

                ts.id AS tipo_servicio_id,
                ts.tipo_serv_nombre,

                dc.empresa_id,
                e.emp_razon_social,
                dc.sucursal_id,
                s.suc_razon_social,

                f.id AS funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido AS encargado,

                tv.id AS tipo_vehiculo_id,
                tv.tip_veh_nombre, tv.tip_veh_capacidad,
                tv.tip_veh_combustible, tv.tip_veh_categoria,

                m.marc_nom, mo.modelo_nom,

                'DIAGNOSTICO NRO: ' || TO_CHAR(dc.id, '0000000') ||
                ' - Cliente: ' || c.cli_nombre || ' ' || c.cli_apellido ||
                ' (' || td.tipo_diag_nombre || ')' AS diagnostico

            FROM diagnostico_cab dc
            JOIN funcionario   f  ON f.id  = dc.funcionario_id
            JOIN empresa       e  ON e.id  = dc.empresa_id
            JOIN sucursal      s  ON s.id  = dc.sucursal_id
            JOIN clientes      c  ON c.id  = dc.clientes_id
            LEFT JOIN tipo_diagnostico td ON td.id = dc.tipo_diagnostico_id
            LEFT JOIN tipo_servicio    ts ON ts.id = dc.tipo_servicio_id
            LEFT JOIN tipo_vehiculo    tv ON tv.id = dc.tipo_vehiculo_id
            LEFT JOIN marca            m  ON m.id  = tv.marca_id
            LEFT JOIN modelo           mo ON mo.id = tv.modelo_id
            WHERE dc.diag_cab_estado IN ('CONFIRMADO')
              AND dc.funcionario_id = ?
              AND (
                c.cli_nombre ILIKE ?
                OR c.cli_apellido ILIKE ?
                OR c.cli_ruc ILIKE ?
                OR td.tipo_diag_nombre ILIKE ?
                OR ts.tipo_serv_nombre ILIKE ?
                OR TO_CHAR(dc.id, '0000000') ILIKE ?
              )
            ORDER BY
                CASE dc.diag_cab_prioridad
                    WHEN 'ALTA'  THEN 1
                    WHEN 'MEDIA' THEN 2
                    WHEN 'BAJA'  THEN 3
                    ELSE 4
                END,
                dc.id DESC
        ", [
            $r->funcionario_id,
            '%' . $r->texto . '%',
            '%' . $r->texto . '%',
            '%' . $r->texto . '%',
            '%' . $r->texto . '%',
            '%' . $r->texto . '%',
            '%' . $r->texto . '%',
        ]);
    }
}
