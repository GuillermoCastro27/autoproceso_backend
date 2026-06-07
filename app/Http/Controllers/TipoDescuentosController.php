<?php

namespace App\Http\Controllers;

use App\Models\TipoDescuentos;
use Illuminate\Http\Request;

class TipoDescuentosController extends Controller
{
    // 📋 Listar todos
    public function read()
    {
        return response()->json(
            TipoDescuentos::select(
                'id as tipo_descuentos_id',
                'tipo_desc_nombre',
                'tipo_desc_descrip',
                'tipo_desc_fechaInicio',
                'tipo_desc_fechaFin'
            )->get()
        );
    }

    // 🆕 Crear tipo de descuento
    public function store(Request $r)
    {
        $datosValidados = $r->validate([
            'tipo_desc_nombre'      => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('tipo_descuentos')
                        ->whereRaw('LOWER(tipo_desc_nombre) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un tipo de descuento con ese nombre.');
                    }
                },
            ],
            'tipo_desc_descrip'     => 'required|string|max:255|not_regex:/[*<>{}|]/',
            'tipo_desc_fechaInicio' => 'required|date_format:d/m/Y H:i:s',
            'tipo_desc_fechaFin'    => 'required|date_format:d/m/Y H:i:s|after_or_equal:tipo_desc_fechaInicio',
        ], [
            'tipo_desc_nombre.required'               => 'El nombre del descuento es obligatorio.',
            'tipo_desc_nombre.not_regex'              => 'El nombre contiene caracteres no permitidos.',
            'tipo_desc_descrip.required'              => 'La descripción es obligatoria.',
            'tipo_desc_descrip.not_regex'             => 'La descripción contiene caracteres no permitidos.',
            'tipo_desc_fechaInicio.required'          => 'Debe indicar la fecha de inicio.',
            'tipo_desc_fechaInicio.date_format'       => 'El formato de la fecha de inicio no es válido.',
            'tipo_desc_fechaFin.required'             => 'Debe indicar la fecha de finalización.',
            'tipo_desc_fechaFin.date_format'          => 'El formato de la fecha de fin no es válido.',
            'tipo_desc_fechaFin.after_or_equal'       => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
        ]);

        $tipodescuentos = TipoDescuentos::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $tipodescuentos
        ], 200);
    }

    // ✏️ Actualizar tipo de descuento
    public function update(Request $r, $id)
    {
        $tipodescuentos = TipoDescuentos::find($id);
        if (!$tipodescuentos) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $datosValidados = $r->validate([
            'tipo_desc_nombre'      => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('tipo_descuentos')
                        ->whereRaw('LOWER(tipo_desc_nombre) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un tipo de descuento con ese nombre.');
                    }
                },
            ],
            'tipo_desc_descrip'     => 'required|string|max:255|not_regex:/[*<>{}|]/',
            'tipo_desc_fechaInicio' => 'required|date',
            'tipo_desc_fechaFin'    => 'required|date|after_or_equal:tipo_desc_fechaInicio',
        ], [
            'tipo_desc_nombre.required'          => 'El nombre del descuento es obligatorio.',
            'tipo_desc_nombre.unique'            => 'Ya existe un tipo de descuento con ese nombre.',
            'tipo_desc_nombre.not_regex'         => 'El nombre contiene caracteres no permitidos.',
            'tipo_desc_descrip.required'         => 'La descripción es obligatoria.',
            'tipo_desc_descrip.not_regex'        => 'La descripción contiene caracteres no permitidos.',
            'tipo_desc_fechaInicio.required'     => 'Debe indicar la fecha de inicio.',
            'tipo_desc_fechaFin.required'        => 'Debe indicar la fecha de finalización.',
            'tipo_desc_fechaFin.after_or_equal'  => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ]);

        $tipodescuentos->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $tipodescuentos
        ], 200);
    }

    public function cambiarEstado($id)
    {
        $tipodescuentos = TipoDescuentos::find($id);
        if (!$tipodescuentos) {
            return response()->json(['mensaje' => 'Tipo de Descuento no encontrado', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($tipodescuentos->tipo_desc_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $tipodescuentos->update(['tipo_desc_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Tipo de Descuento activado con éxito.' : 'Tipo de Descuento desactivado con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
}
