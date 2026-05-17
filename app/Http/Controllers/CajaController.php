<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CajaController extends Controller
{
    public function read()
    {
        return Caja::all();
    }

    public function store(Request $r)
    {
        $r->validate([
            'caja_descripcion' => 'required|string|max:100|unique:cajas,caja_descripcion',
        ], [
            'caja_descripcion.required' => 'La descripción de la caja es obligatoria.',
            'caja_descripcion.max'      => 'La descripción no puede superar los 100 caracteres.',
            'caja_descripcion.unique'   => 'Ya existe una caja con esa descripción.',
        ]);

        $caja = Caja::create([
            'caja_descripcion' => $r->caja_descripcion,
        ]);

        return response()->json([
            'mensaje'  => 'Caja creada con éxito',
            'tipo'     => 'success',
            'registro' => $caja,
        ]);
    }

    public function update(Request $r, $id)
    {
        $caja = Caja::find($id);
        if (!$caja) {
            return response()->json(['mensaje' => 'Caja no encontrada', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'caja_descripcion' => [
                'required', 'string', 'max:100',
                Rule::unique('cajas', 'caja_descripcion')->ignore($id),
            ],
        ], [
            'caja_descripcion.required' => 'La descripción de la caja es obligatoria.',
            'caja_descripcion.max'      => 'La descripción no puede superar los 100 caracteres.',
            'caja_descripcion.unique'   => 'Ya existe otra caja con esa descripción.',
        ]);

        $caja->update([
            'caja_descripcion' => $r->caja_descripcion,
        ]);

        return response()->json([
            'mensaje'  => 'Caja actualizada con éxito',
            'tipo'     => 'success',
            'registro' => $caja,
        ]);
    }

    public function destroy($id)
    {
        $caja = Caja::find($id);
        if (!$caja) {
            return response()->json(['mensaje' => 'Caja no encontrada', 'tipo' => 'error'], 404);
        }

        try {
            $caja->delete();
            return response()->json(['mensaje' => 'Caja eliminada con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar la caja porque tiene movimientos registrados.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
