<?php

namespace App\Http\Controllers;

use App\Models\Tipo;
use Illuminate\Http\Request;

class TipoController extends Controller
{
    public function read()
    {
        return Tipo::all();
    }

    public function store(Request $r)
    {
        $r->validate([
            'tipo_descripcion' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('tipos')
                        ->whereRaw('LOWER(tipo_descripcion) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un tipo con esa descripción.');
                    }
                },
            ],
            'tipo_objeto' => 'required|string|max:100',
        ], [
            'tipo_descripcion.required' => 'La descripción del tipo es obligatoria.',
            'tipo_objeto.required'      => 'El objeto del tipo es obligatorio.',
        ]);

        $tipo = Tipo::create([
            'tipo_descripcion' => $r->tipo_descripcion,
            'tipo_objeto'      => $r->tipo_objeto,
        ]);

        return response()->json([
            'mensaje'  => 'Tipo creado con éxito',
            'tipo'     => 'success',
            'registro' => $tipo,
        ]);
    }

    public function update(Request $r, $id)
    {
        $tipo = Tipo::find($id);
        if (!$tipo) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'tipo_descripcion' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('tipos')
                        ->whereRaw('LOWER(tipo_descripcion) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otro tipo con esa descripción.');
                    }
                },
            ],
            'tipo_objeto' => 'required|string|max:100',
        ], [
            'tipo_descripcion.required' => 'La descripción del tipo es obligatoria.',
            'tipo_objeto.required'      => 'El objeto del tipo es obligatorio.',
        ]);

        $tipo->update([
            'tipo_descripcion' => $r->tipo_descripcion,
            'tipo_objeto'      => $r->tipo_objeto,
        ]);

        return response()->json([
            'mensaje'  => 'Tipo actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $tipo,
        ]);
    }

    public function cambiarEstado($id)
    {
        $tipo = Tipo::find($id);
        if (!$tipo) {
            return response()->json(['mensaje' => 'Tipo no encontrado', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($tipo->tipo_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $tipo->update(['tipo_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Tipo activado con éxito.' : 'Tipo desactivado con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
}
