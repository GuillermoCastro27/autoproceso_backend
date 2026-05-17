<?php

namespace App\Http\Controllers;

use App\Models\Deposito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
                'required', 'string', 'max:200',
                Rule::unique('deposito', 'dep_nombre')->where(fn($q) => $q->where('sucursal_id', $r->sucursal_id)),
            ],
            'sucursal_id' => 'required|integer|exists:sucursal,id',
        ], [
            'dep_nombre.required' => 'El nombre del depósito es obligatorio.',
            'dep_nombre.max'      => 'El nombre no puede superar los 200 caracteres.',
            'dep_nombre.unique'   => 'Ya existe un depósito con ese nombre en la sucursal seleccionada.',
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
                'required', 'string', 'max:200',
                Rule::unique('deposito', 'dep_nombre')
                    ->where(fn($q) => $q->where('sucursal_id', $r->sucursal_id))
                    ->ignore($id),
            ],
            'sucursal_id' => 'required|integer|exists:sucursal,id',
        ], [
            'dep_nombre.required' => 'El nombre del depósito es obligatorio.',
            'dep_nombre.max'      => 'El nombre no puede superar los 200 caracteres.',
            'dep_nombre.unique'   => 'Ya existe otro depósito con ese nombre en la sucursal seleccionada.',
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

    public function destroy($id)
    {
        $deposito = Deposito::find($id);
        if (!$deposito) {
            return response()->json(['mensaje' => 'Depósito no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $deposito->delete();
            return response()->json(['mensaje' => 'Depósito eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el depósito porque tiene stock o movimientos de inventario asociados.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
