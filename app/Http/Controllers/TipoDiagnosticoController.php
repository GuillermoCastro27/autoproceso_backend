<?php

namespace App\Http\Controllers;

use App\Models\TipoDiagnostico;
use Illuminate\Http\Request;

class TipoDiagnosticoController extends Controller
{
    public function read()
    {
        return response()->json(
            TipoDiagnostico::select(
                'id as tipo_diagnostico_id',
                'tipo_diag_nombre',
                'tipo_diag_descrip'
            )->get()
        );
    }

    public function store(Request $r)
    {
        $r->validate([
            'tipo_diag_nombre' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('tipo_diagnostico')
                        ->whereRaw('LOWER(tipo_diag_nombre) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un tipo de diagnóstico con ese nombre.');
                    }
                },
            ],
            'tipo_diag_descrip' => 'required|string|max:255|not_regex:/[*<>{}|]/',
        ], [
            'tipo_diag_nombre.required'  => 'El nombre del tipo de diagnóstico es obligatorio.',
            'tipo_diag_nombre.max'       => 'El nombre no puede superar los 100 caracteres.',
            'tipo_diag_nombre.not_regex' => 'El nombre contiene caracteres no permitidos.',
            'tipo_diag_descrip.required' => 'La descripción es obligatoria.',
            'tipo_diag_descrip.max'      => 'La descripción no puede superar los 255 caracteres.',
            'tipo_diag_descrip.not_regex'=> 'La descripción contiene caracteres no permitidos.',
        ]);

        $registro = TipoDiagnostico::create([
            'tipo_diag_nombre' => $r->tipo_diag_nombre,
            'tipo_diag_descrip' => $r->tipo_diag_descrip,
        ]);

        return response()->json([
            'mensaje'  => 'Tipo de diagnóstico creado con éxito',
            'tipo'     => 'success',
            'registro' => $registro
        ]);
    }

    public function update(Request $r, $id)
    {
        $registro = TipoDiagnostico::find($id);

        if (!$registro) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'tipo_diag_nombre' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('tipo_diagnostico')
                        ->whereRaw('LOWER(tipo_diag_nombre) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otro tipo de diagnóstico con ese nombre.');
                    }
                },
            ],
            'tipo_diag_descrip' => 'required|string|max:255|not_regex:/[*<>{}|]/',
        ], [
            'tipo_diag_nombre.required'  => 'El nombre del tipo de diagnóstico es obligatorio.',
            'tipo_diag_nombre.max'       => 'El nombre no puede superar los 100 caracteres.',
            'tipo_diag_nombre.unique'    => 'Ya existe otro tipo de diagnóstico con ese nombre.',
            'tipo_diag_nombre.not_regex' => 'El nombre contiene caracteres no permitidos.',
            'tipo_diag_descrip.required' => 'La descripción es obligatoria.',
            'tipo_diag_descrip.max'      => 'La descripción no puede superar los 255 caracteres.',
            'tipo_diag_descrip.not_regex'=> 'La descripción contiene caracteres no permitidos.',
        ]);

        $registro->update([
            'tipo_diag_nombre' => $r->tipo_diag_nombre,
            'tipo_diag_descrip' => $r->tipo_diag_descrip,
        ]);

        return response()->json([
            'mensaje'  => 'Tipo de diagnóstico actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $registro
        ]);
    }

    public function cambiarEstado($id)
    {
        $registro = TipoDiagnostico::find($id);
        if (!$registro) {
            return response()->json(['mensaje' => 'Tipo de Diagnóstico no encontrado', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($registro->tipo_diag_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $registro->update(['tipo_diag_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Tipo de Diagnóstico activado con éxito.' : 'Tipo de Diagnóstico desactivado con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
}
