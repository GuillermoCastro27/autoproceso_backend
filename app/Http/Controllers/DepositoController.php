<?php

namespace App\Http\Controllers;

use App\Models\Deposito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositoController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT d.id, d.dep_nombre, d.sucursal_id, s.suc_razon_social
            FROM deposito d
            LEFT JOIN sucursal s ON s.id = d.sucursal_id
            ORDER BY d.dep_nombre
        ");
    }

    public function readBySucursal($sucursal_id)
    {
        return DB::select("
            SELECT d.id, d.dep_nombre, d.sucursal_id, s.suc_razon_social
            FROM deposito d
            LEFT JOIN sucursal s ON s.id = d.sucursal_id
            WHERE d.sucursal_id = ?
            ORDER BY d.dep_nombre
        ", [$sucursal_id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'dep_nombre'  => [
                'required', 'string', 'max:200', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('deposito')
                        ->whereRaw('LOWER(dep_nombre) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un depósito con ese nombre.');
                    }
                },
            ],
            'sucursal_id' => 'required|integer|exists:sucursal,id',
        ], [
            'dep_nombre.required' => 'El nombre del depósito es obligatorio.',
            'dep_nombre.max'      => 'El nombre no puede superar los 200 caracteres.',
            'sucursal_id.required'=> 'Debe seleccionar una sucursal.',
            'sucursal_id.exists'  => 'La sucursal seleccionada no existe.',
        ]);

        $deposito = Deposito::create([
            'dep_nombre'  => $r->dep_nombre,
            'sucursal_id' => $r->sucursal_id,
        ]);

        return response()->json([
            'mensaje'  => 'Depósito creado con éxito',
            'tipo'     => 'success',
            'registro' => $deposito,
        ]);
    }

    public function update(Request $r, $id)
    {
        $deposito = Deposito::find($id);
        if (!$deposito) {
            return response()->json(['mensaje' => 'Depósito no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'dep_nombre'  => [
                'required', 'string', 'max:200', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('deposito')
                        ->whereRaw('LOWER(dep_nombre) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otro depósito con ese nombre.');
                    }
                },
            ],
            'sucursal_id' => 'required|integer|exists:sucursal,id',
        ], [
            'dep_nombre.required' => 'El nombre del depósito es obligatorio.',
            'dep_nombre.max'      => 'El nombre no puede superar los 200 caracteres.',
            'sucursal_id.required'=> 'Debe seleccionar una sucursal.',
            'sucursal_id.exists'  => 'La sucursal seleccionada no existe.',
        ]);

        $deposito->update([
            'dep_nombre'  => $r->dep_nombre,
            'sucursal_id' => $r->sucursal_id,
        ]);

        return response()->json([
            'mensaje'  => 'Depósito actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $deposito,
        ]);
    }

    public function cambiarEstado($id)
    {
        $deposito = Deposito::find($id);
        if (!$deposito) {
            return response()->json(['mensaje' => 'Depósito no encontrado', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($deposito->dep_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $deposito->update(['dep_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Depósito activado con éxito.' : 'Depósito desactivado con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
}
