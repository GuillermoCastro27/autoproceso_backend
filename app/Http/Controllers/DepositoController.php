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
            'dep_nombre'  => 'required|string|max:200',
            'sucursal_id' => 'required|integer|exists:sucursal,id',
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
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'dep_nombre'  => 'required|string|max:200',
            'sucursal_id' => 'required|integer|exists:sucursal,id',
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
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $deposito->delete();

        return response()->json(['mensaje' => 'Depósito eliminado con éxito', 'tipo' => 'success']);
    }
}
