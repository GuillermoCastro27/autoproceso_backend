<?php

namespace App\Http\Controllers;

use App\Models\MarcaTarjeta;
use Illuminate\Http\Request;

class MarcaTarjetaController extends Controller
{
    public function read()
    {
        return response()->json(
            MarcaTarjeta::select(
                'id as marca_tarjeta_id',
                'marca_nombre'
            )->get()
        );
    }

    public function store(Request $r)
    {
        $datos = $r->validate([
            'marca_nombre' => 'required|string|max:100|unique:marca_tarjeta,marca_nombre'
        ], [
            'marca_nombre.required' => 'El nombre de la marca es obligatorio.',
            'marca_nombre.unique'   => 'La marca ya existe.'
        ]);

        $marca = MarcaTarjeta::create($datos);

        return response()->json([
            'mensaje'  => 'Marca registrada con éxito',
            'tipo'     => 'success',
            'registro' => $marca
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $marca = MarcaTarjeta::find($id);

        if (!$marca) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $datos = $r->validate([
            'marca_nombre' => 'required|string|max:100|unique:marca_tarjeta,marca_nombre,' . $id
        ], [
            'marca_nombre.required' => 'El nombre de la marca es obligatorio.',
            'marca_nombre.unique'   => 'La marca ya existe.'
        ]);

        $marca->update($datos);

        return response()->json([
            'mensaje'  => 'Marca modificada con éxito',
            'tipo'     => 'success',
            'registro' => $marca
        ], 200);
    }

    public function destroy($id)
    {
        $marca = MarcaTarjeta::find($id);

        if (!$marca) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $marca->delete();

        return response()->json([
            'mensaje' => 'Marca eliminada con éxito',
            'tipo'    => 'success'
        ], 200);
    }
}
