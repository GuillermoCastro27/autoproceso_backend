<?php

namespace App\Http\Controllers;

use App\Models\PromocionesCab;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PromocionesCabController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                pc.id,
                pc.prom_cab_nombre,
                pc.prom_cab_observaciones,
                TO_CHAR(pc.prom_cab_fecha_registro, 'DD/MM/YYYY HH24:MI:SS') AS prom_cab_fecha_registro,
                TO_CHAR(pc.prom_cab_fecha_inicio,   'DD/MM/YYYY HH24:MI:SS') AS prom_cab_fecha_inicio,
                TO_CHAR(pc.prom_cab_fecha_fin,       'DD/MM/YYYY HH24:MI:SS') AS prom_cab_fecha_fin,
                pc.prom_cab_estado,
                pc.sucursal_id,
                s.suc_razon_social,
                pc.empresa_id,
                e.emp_razon_social,
                pc.funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                pc.tipo_promociones_id,
                tp.tipo_prom_nombre,
                tp.tipo_prom_modo,
                tp.tipo_prom_valor,
                pc.created_at,
                pc.updated_at
            FROM promociones_cab pc
            JOIN sucursal s         ON s.id  = pc.sucursal_id
            JOIN empresa e          ON e.id  = pc.empresa_id
            JOIN funcionario f      ON f.id  = pc.funcionario_id
            JOIN tipo_promociones tp ON tp.id = pc.tipo_promociones_id
            ORDER BY pc.id DESC
        ");
    }

    private function validationRules(): array
    {
        return [
            'prom_cab_nombre'          => ['required', 'string', 'max:200', 'not_regex:/[*<>{}|]/'],
            'prom_cab_observaciones'   => ['nullable', 'string', 'max:500', 'not_regex:/[*<>{}|]/'],
            'prom_cab_fecha_registro'  => 'required|date_format:d/m/Y H:i:s',
            'prom_cab_fecha_inicio'    => 'required|date_format:d/m/Y H:i:s',
            'prom_cab_fecha_fin'       => 'required|date_format:d/m/Y H:i:s|after_or_equal:prom_cab_fecha_inicio',
            'tipo_promociones_id'      => 'required|integer|exists:tipo_promociones,id',
            'empresa_id'               => 'required|integer|exists:empresa,id',
            'sucursal_id'              => 'required|integer|exists:sucursal,id',
        ];
    }

    private function validationMessages(): array
    {
        return [
            'prom_cab_nombre.required'           => 'El nombre de la promoción es obligatorio.',
            'prom_cab_nombre.not_regex'          => 'El nombre contiene caracteres no permitidos.',
            'prom_cab_observaciones.not_regex'   => 'Las observaciones contienen caracteres no permitidos.',
            'prom_cab_fecha_registro.required'   => 'La fecha de registro es obligatoria.',
            'prom_cab_fecha_registro.date_format'=> 'El formato de fecha de registro no es válido (DD/MM/YYYY HH:MM:SS).',
            'prom_cab_fecha_inicio.required'     => 'La fecha de inicio es obligatoria.',
            'prom_cab_fecha_inicio.date_format'  => 'El formato de fecha de inicio no es válido (DD/MM/YYYY HH:MM:SS).',
            'prom_cab_fecha_fin.required'        => 'La fecha de fin es obligatoria.',
            'prom_cab_fecha_fin.date_format'     => 'El formato de fecha de fin no es válido (DD/MM/YYYY HH:MM:SS).',
            'prom_cab_fecha_fin.after_or_equal'  => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'tipo_promociones_id.required'       => 'Debe seleccionar un tipo de promoción.',
            'tipo_promociones_id.exists'         => 'El tipo de promoción seleccionado no es válido.',
            'empresa_id.required'                => 'La empresa es obligatoria.',
            'sucursal_id.required'               => 'La sucursal es obligatoria.',
        ];
    }

    public function store(Request $r)
    {
        $r->validate($this->validationRules(), $this->validationMessages());

        $promocioncab = PromocionesCab::create([
            'prom_cab_nombre'         => $r->prom_cab_nombre,
            'prom_cab_observaciones'  => $r->prom_cab_observaciones,
            'prom_cab_fecha_registro' => $r->prom_cab_fecha_registro,
            'prom_cab_fecha_inicio'   => $r->prom_cab_fecha_inicio,
            'prom_cab_fecha_fin'      => $r->prom_cab_fecha_fin,
            'prom_cab_estado'         => 'PENDIENTE',
            'tipo_promociones_id'     => $r->tipo_promociones_id,
            'empresa_id'              => $r->empresa_id,
            'sucursal_id'             => $r->sucursal_id,
            'funcionario_id'          => auth()->user()->funcionario_id,
        ]);

        return response()->json([
            'mensaje'  => 'Promoción registrada con éxito',
            'tipo'     => 'success',
            'registro' => $promocioncab,
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $promocioncab = PromocionesCab::find($id);
        if (!$promocioncab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($promocioncab->prom_cab_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden modificar promociones en estado PENDIENTE.',
                'tipo'    => 'warning',
            ], 200);
        }

        $r->validate($this->validationRules(), $this->validationMessages());

        $promocioncab->update([
            'prom_cab_nombre'         => $r->prom_cab_nombre,
            'prom_cab_observaciones'  => $r->prom_cab_observaciones,
            'prom_cab_fecha_registro' => $r->prom_cab_fecha_registro,
            'prom_cab_fecha_inicio'   => $r->prom_cab_fecha_inicio,
            'prom_cab_fecha_fin'      => $r->prom_cab_fecha_fin,
            'tipo_promociones_id'     => $r->tipo_promociones_id,
            'empresa_id'              => $r->empresa_id,
            'sucursal_id'             => $r->sucursal_id,
        ]);

        return response()->json([
            'mensaje'  => 'Promoción modificada con éxito',
            'tipo'     => 'success',
            'registro' => $promocioncab,
        ], 200);
    }

    public function anular(Request $r, $id)
    {
        $promocioncab = PromocionesCab::find($id);
        if (!$promocioncab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($promocioncab->prom_cab_estado === 'ANULADO') {
            return response()->json(['mensaje' => 'La promoción ya se encuentra anulada.', 'tipo' => 'warning'], 200);
        }

        $promocioncab->prom_cab_estado = 'ANULADO';
        $promocioncab->save();

        return response()->json([
            'mensaje'  => 'Promoción anulada con éxito',
            'tipo'     => 'success',
            'registro' => $promocioncab,
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $promocioncab = PromocionesCab::find($id);
        if (!$promocioncab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($promocioncab->prom_cab_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden confirmar promociones en estado PENDIENTE.',
                'tipo'    => 'warning',
            ], 200);
        }

        $promocioncab->prom_cab_estado = 'CONFIRMADO';
        $promocioncab->save();

        return response()->json([
            'mensaje'  => 'Promoción confirmada con éxito',
            'tipo'     => 'success',
            'registro' => $promocioncab,
        ], 200);
    }

    public function buscar(Request $r)
    {
        $texto  = '%' . ($r->texto ?? '') . '%';
        $funcId = $r->funcionario_id;

        DB::update("
            UPDATE promociones_cab
            SET prom_cab_estado = 'ANULADO'
            WHERE prom_cab_estado = 'CONFIRMADO'
            AND prom_cab_fecha_fin < CURRENT_TIMESTAMP
        ");

        return DB::select("
            SELECT
                pc.id AS promociones_cab_id,
                pc.prom_cab_nombre,
                pc.prom_cab_observaciones,
                TO_CHAR(pc.prom_cab_fecha_registro, 'DD/MM/YYYY HH24:MI:SS') AS prom_cab_fecha_registro,
                TO_CHAR(pc.prom_cab_fecha_inicio,   'DD/MM/YYYY HH24:MI:SS') AS prom_cab_fecha_inicio,
                TO_CHAR(pc.prom_cab_fecha_fin,       'DD/MM/YYYY HH24:MI:SS') AS prom_cab_fecha_fin,
                pc.prom_cab_estado,
                pc.tipo_promociones_id,
                tp.tipo_prom_nombre,
                f.id AS funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido AS encargado,
                pc.empresa_id,
                e.emp_razon_social,
                pc.sucursal_id,
                s.suc_razon_social,
                'PROMOCIÓN NRO: ' || TO_CHAR(pc.id, '0000000') ||
                ' (' || pc.prom_cab_nombre || ')' AS prom_texto
            FROM promociones_cab pc
            JOIN funcionario f      ON f.id  = pc.funcionario_id
            JOIN empresa e          ON e.id  = pc.empresa_id
            JOIN sucursal s         ON s.id  = pc.sucursal_id
            JOIN tipo_promociones tp ON tp.id = pc.tipo_promociones_id
            WHERE pc.prom_cab_estado = 'CONFIRMADO'
            AND pc.funcionario_id = ?
            AND CURRENT_TIMESTAMP BETWEEN pc.prom_cab_fecha_inicio AND pc.prom_cab_fecha_fin
            AND (
                pc.prom_cab_nombre ILIKE ?
                OR pc.prom_cab_observaciones ILIKE ?
                OR tp.tipo_prom_nombre ILIKE ?
                OR TO_CHAR(pc.id, '0000000') ILIKE ?
            )
            ORDER BY pc.id DESC
        ", [$funcId, $texto, $texto, $texto, $texto]);
    }
}
