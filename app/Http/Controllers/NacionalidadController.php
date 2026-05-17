<?php

namespace App\Http\Controllers;

use App\Models\Nacionalidad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NacionalidadController extends Controller
{
    public function read()
    {
        return Nacionalidad::all();
    }

    public function store(Request $r)
    {
        $r->validate([
            'nacio_descripcion' => 'required|string|max:100|unique:nacionalidad,nacio_descripcion',
        ], [
            'nacio_descripcion.required' => 'La descripción de la nacionalidad es obligatoria.',
            'nacio_descripcion.max'      => 'La descripción no puede superar los 100 caracteres.',
            'nacio_descripcion.unique'   => 'Ya existe una nacionalidad con esa descripción.',
        ]);

        $nacionalidad = Nacionalidad::create([
            'nacio_descripcion' => $r->nacio_descripcion,
        ]);

        return response()->json([
            'mensaje'  => 'Nacionalidad creada con éxito',
            'tipo'     => 'success',
            'registro' => $nacionalidad,
        ]);
    }

    public function update(Request $r, $id)
    {
        $nacionalidad = Nacionalidad::find($id);
        if (!$nacionalidad) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'nacio_descripcion' => [
                'required', 'string', 'max:100',
                Rule::unique('nacionalidad', 'nacio_descripcion')->ignore($id),
            ],
        ], [
            'nacio_descripcion.required' => 'La descripción de la nacionalidad es obligatoria.',
            'nacio_descripcion.max'      => 'La descripción no puede superar los 100 caracteres.',
            'nacio_descripcion.unique'   => 'Ya existe otra nacionalidad con esa descripción.',
        ]);

        $nacionalidad->update([
            'nacio_descripcion' => $r->nacio_descripcion,
        ]);

        return response()->json([
            'mensaje'  => 'Nacionalidad actualizada con éxito',
            'tipo'     => 'success',
            'registro' => $nacionalidad,
        ]);
    }

    public function destroy($id)
    {
        $nacionalidad = Nacionalidad::find($id);
        if (!$nacionalidad) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $nacionalidad->delete();
            return response()->json(['mensaje' => 'Nacionalidad eliminada con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar la nacionalidad porque está siendo utilizada en el sistema.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
