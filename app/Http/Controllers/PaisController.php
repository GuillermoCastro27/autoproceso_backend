<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaisController extends Controller
{
    public function read()
    {
        return Pais::all();
    }

    public function store(Request $r)
    {
        $r->validate([
            'pais_descrpcion' => 'required|string|max:100|unique:paises,pais_descrpcion',
            'pais_gentilicio' => 'required|string|max:100',
            'pais_siglas'     => 'required|string|max:10|unique:paises,pais_siglas',
        ], [
            'pais_descrpcion.required' => 'El nombre del país es obligatorio.',
            'pais_descrpcion.unique'   => 'Ya existe un país con ese nombre.',
            'pais_gentilicio.required' => 'El gentilicio es obligatorio.',
            'pais_siglas.required'     => 'Las siglas son obligatorias.',
            'pais_siglas.unique'       => 'Ya existe un país con esas siglas.',
        ]);

        $pais = Pais::create([
            'pais_descrpcion' => $r->pais_descrpcion,
            'pais_gentilicio' => $r->pais_gentilicio,
            'pais_siglas'     => $r->pais_siglas,
        ]);

        return response()->json([
            'mensaje'  => 'País creado con éxito',
            'tipo'     => 'success',
            'registro' => $pais,
        ]);
    }

    public function update(Request $r, $id)
    {
        $pais = Pais::find($id);
        if (!$pais) {
            return response()->json(['mensaje' => 'País no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'pais_descrpcion' => ['required', 'string', 'max:100', Rule::unique('paises', 'pais_descrpcion')->ignore($id)],
            'pais_gentilicio' => 'required|string|max:100',
            'pais_siglas'     => ['required', 'string', 'max:10', Rule::unique('paises', 'pais_siglas')->ignore($id)],
        ], [
            'pais_descrpcion.required' => 'El nombre del país es obligatorio.',
            'pais_descrpcion.unique'   => 'Ya existe otro país con ese nombre.',
            'pais_gentilicio.required' => 'El gentilicio es obligatorio.',
            'pais_siglas.required'     => 'Las siglas son obligatorias.',
            'pais_siglas.unique'       => 'Ya existe otro país con esas siglas.',
        ]);

        $pais->update([
            'pais_descrpcion' => $r->pais_descrpcion,
            'pais_gentilicio' => $r->pais_gentilicio,
            'pais_siglas'     => $r->pais_siglas,
        ]);

        return response()->json([
            'mensaje'  => 'País actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $pais,
        ]);
    }

    public function destroy($id)
    {
        $pais = Pais::find($id);
        if (!$pais) {
            return response()->json(['mensaje' => 'País no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $pais->delete();
            return response()->json(['mensaje' => 'País eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el país porque tiene ciudades u otros registros asociados.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
