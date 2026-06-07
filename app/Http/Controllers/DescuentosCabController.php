<?php

namespace App\Http\Controllers;

use App\Models\DescuentosCab;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DescuentosCabController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                dc.id,
                dc.desc_cab_nombre,
                dc.desc_cab_observaciones,
                TO_CHAR(dc.desc_cab_fecha_registro, 'DD/MM/YYYY HH24:MI:SS') AS desc_cab_fecha_registro,
                TO_CHAR(dc.desc_cab_fecha_inicio,   'DD/MM/YYYY HH24:MI:SS') AS desc_cab_fecha_inicio,
                TO_CHAR(dc.desc_cab_fecha_fin,       'DD/MM/YYYY HH24:MI:SS') AS desc_cab_fecha_fin,
                dc.desc_cab_estado,
                dc.desc_cab_porcentaje,
                dc.sucursal_id,
                s.suc_razon_social,
                dc.empresa_id,
                e.emp_razon_social,
                dc.funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                dc.tipo_descuentos_id,
                td.tipo_desc_nombre,
                dc.created_at,
                dc.updated_at
            FROM descuentos_cab dc
            JOIN sucursal s         ON s.id  = dc.sucursal_id
            JOIN empresa e          ON e.id  = dc.empresa_id
            JOIN funcionario f      ON f.id  = dc.funcionario_id
            JOIN tipo_descuentos td ON td.id = dc.tipo_descuentos_id
            ORDER BY dc.id DESC
        ");
    }

    private function validationRules(): array
    {
        return [
            'desc_cab_nombre'         => ['required', 'string', 'max:200', 'not_regex:/[*<>{}|]/'],
            'desc_cab_observaciones'  => ['nullable', 'string', 'max:500', 'not_regex:/[*<>{}|]/'],
            'desc_cab_fecha_registro' => 'required|date_format:d/m/Y H:i:s',
            'desc_cab_fecha_inicio'   => 'required|date_format:d/m/Y H:i:s',
            'desc_cab_fecha_fin'      => 'required|date_format:d/m/Y H:i:s|after_or_equal:desc_cab_fecha_inicio',
            'desc_cab_porcentaje'     => 'required|numeric|min:0|max:100',
            'tipo_descuentos_id'      => 'required|integer|exists:tipo_descuentos,id',
            'empresa_id'              => 'required|integer|exists:empresa,id',
            'sucursal_id'             => 'required|integer|exists:sucursal,id',
        ];
    }

    private function validationMessages(): array
    {
        return [
            'desc_cab_nombre.required'           => 'El nombre del descuento es obligatorio.',
            'desc_cab_nombre.not_regex'          => 'El nombre contiene caracteres no permitidos.',
            'desc_cab_observaciones.not_regex'   => 'Las observaciones contienen caracteres no permitidos.',
            'desc_cab_fecha_registro.required'   => 'La fecha de registro es obligatoria.',
            'desc_cab_fecha_registro.date_format'=> 'El formato de fecha de registro no es válido (DD/MM/YYYY HH:MM:SS).',
            'desc_cab_fecha_inicio.required'     => 'La fecha de inicio es obligatoria.',
            'desc_cab_fecha_inicio.date_format'  => 'El formato de fecha de inicio no es válido (DD/MM/YYYY HH:MM:SS).',
            'desc_cab_fecha_fin.required'        => 'La fecha de fin es obligatoria.',
            'desc_cab_fecha_fin.date_format'     => 'El formato de fecha de fin no es válido (DD/MM/YYYY HH:MM:SS).',
            'desc_cab_fecha_fin.after_or_equal'  => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'desc_cab_porcentaje.required'       => 'El porcentaje es obligatorio.',
            'desc_cab_porcentaje.numeric'        => 'El porcentaje debe ser un número.',
            'desc_cab_porcentaje.min'            => 'El porcentaje no puede ser negativo.',
            'desc_cab_porcentaje.max'            => 'El porcentaje no puede superar 100.',
            'tipo_descuentos_id.required'        => 'Debe seleccionar un tipo de descuento.',
            'tipo_descuentos_id.exists'          => 'El tipo de descuento seleccionado no es válido.',
            'empresa_id.required'                => 'La empresa es obligatoria.',
            'sucursal_id.required'               => 'La sucursal es obligatoria.',
        ];
    }

    public function store(Request $r)
    {
        $r->validate($this->validationRules(), $this->validationMessages());

        $descuentoscab = DescuentosCab::create([
            'desc_cab_nombre'         => $r->desc_cab_nombre,
            'desc_cab_observaciones'  => $r->desc_cab_observaciones,
            'desc_cab_fecha_registro' => $r->desc_cab_fecha_registro,
            'desc_cab_fecha_inicio'   => $r->desc_cab_fecha_inicio,
            'desc_cab_fecha_fin'      => $r->desc_cab_fecha_fin,
            'desc_cab_estado'         => 'PENDIENTE',
            'desc_cab_porcentaje'     => $r->desc_cab_porcentaje,
            'tipo_descuentos_id'      => $r->tipo_descuentos_id,
            'empresa_id'              => $r->empresa_id,
            'sucursal_id'             => $r->sucursal_id,
            'funcionario_id'          => auth()->user()->funcionario_id,
        ]);

        return response()->json([
            'mensaje'  => 'Descuento registrado con éxito',
            'tipo'     => 'success',
            'registro' => $descuentoscab,
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $descuentoscab = DescuentosCab::find($id);
        if (!$descuentoscab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($descuentoscab->desc_cab_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden modificar descuentos en estado PENDIENTE.',
                'tipo'    => 'warning',
            ], 200);
        }

        $r->validate($this->validationRules(), $this->validationMessages());

        $descuentoscab->update([
            'desc_cab_nombre'         => $r->desc_cab_nombre,
            'desc_cab_observaciones'  => $r->desc_cab_observaciones,
            'desc_cab_fecha_registro' => $r->desc_cab_fecha_registro,
            'desc_cab_fecha_inicio'   => $r->desc_cab_fecha_inicio,
            'desc_cab_fecha_fin'      => $r->desc_cab_fecha_fin,
            'desc_cab_porcentaje'     => $r->desc_cab_porcentaje,
            'tipo_descuentos_id'      => $r->tipo_descuentos_id,
            'empresa_id'              => $r->empresa_id,
            'sucursal_id'             => $r->sucursal_id,
        ]);

        return response()->json([
            'mensaje'  => 'Descuento modificado con éxito',
            'tipo'     => 'success',
            'registro' => $descuentoscab,
        ], 200);
    }

    public function anular(Request $r, $id)
    {
        $descuentoscab = DescuentosCab::find($id);
        if (!$descuentoscab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($descuentoscab->desc_cab_estado === 'ANULADO') {
            return response()->json(['mensaje' => 'El descuento ya se encuentra anulado.', 'tipo' => 'warning'], 200);
        }

        $descuentoscab->desc_cab_estado = 'ANULADO';
        $descuentoscab->save();

        return response()->json([
            'mensaje'  => 'Descuento anulado con éxito',
            'tipo'     => 'success',
            'registro' => $descuentoscab,
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $descuentoscab = DescuentosCab::find($id);
        if (!$descuentoscab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($descuentoscab->desc_cab_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden confirmar descuentos en estado PENDIENTE.',
                'tipo'    => 'warning',
            ], 200);
        }

        $descuentoscab->desc_cab_estado = 'CONFIRMADO';
        $descuentoscab->save();

        return response()->json([
            'mensaje'  => 'Descuento confirmado con éxito',
            'tipo'     => 'success',
            'registro' => $descuentoscab,
        ], 200);
    }

    public function buscar(Request $r)
    {
        $texto  = '%' . ($r->texto ?? '') . '%';
        $funcId = $r->funcionario_id;

        DB::update("
            UPDATE descuentos_cab
            SET desc_cab_estado = 'ANULADO'
            WHERE desc_cab_estado = 'CONFIRMADO'
            AND desc_cab_fecha_fin < CURRENT_TIMESTAMP
        ");

        return DB::select("
            SELECT
                dc.id AS descuentos_cab_id,
                dc.desc_cab_nombre,
                dc.desc_cab_observaciones,
                TO_CHAR(dc.desc_cab_fecha_registro, 'DD/MM/YYYY HH24:MI:SS') AS desc_cab_fecha_registro,
                TO_CHAR(dc.desc_cab_fecha_inicio,   'DD/MM/YYYY HH24:MI:SS') AS desc_cab_fecha_inicio,
                TO_CHAR(dc.desc_cab_fecha_fin,       'DD/MM/YYYY HH24:MI:SS') AS desc_cab_fecha_fin,
                dc.desc_cab_estado,
                dc.desc_cab_porcentaje,
                dc.tipo_descuentos_id,
                td.tipo_desc_nombre,
                f.id AS funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido AS encargado,
                dc.empresa_id,
                e.emp_razon_social,
                dc.sucursal_id,
                s.suc_razon_social,
                'DESCUENTO NRO: ' || TO_CHAR(dc.id, '0000000') ||
                ' (' || dc.desc_cab_nombre || ')' AS desc_texto
            FROM descuentos_cab dc
            JOIN funcionario f      ON f.id  = dc.funcionario_id
            JOIN empresa e          ON e.id  = dc.empresa_id
            JOIN sucursal s         ON s.id  = dc.sucursal_id
            JOIN tipo_descuentos td ON td.id = dc.tipo_descuentos_id
            WHERE dc.desc_cab_estado = 'CONFIRMADO'
            AND dc.funcionario_id = ?
            AND CURRENT_TIMESTAMP BETWEEN dc.desc_cab_fecha_inicio AND dc.desc_cab_fecha_fin
            AND (
                dc.desc_cab_nombre ILIKE ?
                OR dc.desc_cab_observaciones ILIKE ?
                OR td.tipo_desc_nombre ILIKE ?
                OR TO_CHAR(dc.id, '0000000') ILIKE ?
            )
            ORDER BY dc.id DESC
        ", [$funcId, $texto, $texto, $texto, $texto]);
    }
}
