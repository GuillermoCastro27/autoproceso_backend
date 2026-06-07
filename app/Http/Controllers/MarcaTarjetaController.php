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
        $r->validate([
            'marca_nombre' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('marca_tarjeta')
                        ->whereRaw('LOWER(marca_nombre) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe una marca de tarjeta con ese nombre.');
                    }
                },
            ],
        ], [
            'marca_nombre.required' => 'El nombre de la marca es obligatorio.',
        ]);
        $datos = $r->only(['marca_nombre']);

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

        $r->validate([
            'marca_nombre' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('marca_tarjeta')
                        ->whereRaw('LOWER(marca_nombre) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otra marca de tarjeta con ese nombre.');
                    }
                },
            ],
        ], [
            'marca_nombre.required' => 'El nombre de la marca es obligatorio.',
        ]);
        $datos = $r->only(['marca_nombre']);

        $marca->update($datos);

        return response()->json([
            'mensaje'  => 'Marca modificada con éxito',
            'tipo'     => 'success',
            'registro' => $marca
        ], 200);
    }

    public function cambiarEstado($id)
    {
        $marca = MarcaTarjeta::find($id);
        if (!$marca) {
            return response()->json(['mensaje' => 'Marca de Tarjeta no encontrada', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($marca->marca_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $marca->update(['marca_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Marca de Tarjeta activada con éxito.' : 'Marca de Tarjeta desactivada con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
}
