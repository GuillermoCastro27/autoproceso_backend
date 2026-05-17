<?php

namespace App\Http\Controllers;

use App\Models\Tipo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
                'required', 'string', 'max:100',
                Rule::unique('tipos', 'tipo_descripcion')->where(fn($q) => $q->where('tipo_objeto', $r->tipo_objeto)),
            ],
            'tipo_objeto' => 'required|string|max:100',
        ], [
            'tipo_descripcion.required' => 'La descripción del tipo es obligatoria.',
            'tipo_descripcion.unique'   => 'Ya existe un tipo con esa descripción para el mismo objeto.',
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
                'required', 'string', 'max:100',
                Rule::unique('tipos', 'tipo_descripcion')
                    ->where(fn($q) => $q->where('tipo_objeto', $r->tipo_objeto))
                    ->ignore($id),
            ],
            'tipo_objeto' => 'required|string|max:100',
        ], [
            'tipo_descripcion.required' => 'La descripción del tipo es obligatoria.',
            'tipo_descripcion.unique'   => 'Ya existe otro tipo con esa descripción para el mismo objeto.',
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

    public function destroy($id)
    {
        $tipo = Tipo::find($id);
        if (!$tipo) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $tipo->delete();
            return response()->json(['mensaje' => 'Tipo eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el tipo porque está siendo utilizado en el sistema.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
