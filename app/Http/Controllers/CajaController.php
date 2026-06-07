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
            'caja_descripcion' => 'required|string|max:100|unique:cajas,caja_descripcion|not_regex:/[*<>{}|]/',
        ], [
            'caja_descripcion.required'  => 'La descripción de la caja es obligatoria.',
            'caja_descripcion.max'       => 'La descripción no puede superar los 100 caracteres.',
            'caja_descripcion.unique'    => 'Ya existe una caja con esa descripción.',
            'caja_descripcion.not_regex' => 'La descripción contiene caracteres no permitidos.',
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
                'not_regex:/[*<>{}|]/',
            ],
        ], [
            'caja_descripcion.required'  => 'La descripción de la caja es obligatoria.',
            'caja_descripcion.max'       => 'La descripción no puede superar los 100 caracteres.',
            'caja_descripcion.unique'    => 'Ya existe otra caja con esa descripción.',
            'caja_descripcion.not_regex' => 'La descripción contiene caracteres no permitidos.',
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

    public function cambiarEstado($id)
    {
        $caja = Caja::find($id);
        if (!$caja) {
            return response()->json(['mensaje' => 'Caja no encontrada', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($caja->caja_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $caja->update(['caja_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Caja activada con éxito.' : 'Caja desactivada con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
}
