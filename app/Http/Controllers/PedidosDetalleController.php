<?php

namespace App\Http\Controllers;

use App\Models\PedidosDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidosDetalleController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT
                pd.pedidos_id,
                pd.item_id,
                pd.deposito_id,
                pd.det_cantidad,
                pd.cantidad_stock,
                pd.marca_id,
                pd.modelo_id,
                i.item_decripcion,
                COALESCE(d.dep_nombre, '-')    AS dep_nombre,
                COALESCE(ma.marc_nom, '')       AS marc_nom,
                COALESCE(mo.modelo_nom, '')     AS modelo_nom,
                COALESCE(mo.modelo_año::varchar,'') AS modelo_año
            FROM pedidos_detalles pd
            JOIN items i ON i.id = pd.item_id
            LEFT JOIN deposito d  ON d.id  = pd.deposito_id
            LEFT JOIN marca ma    ON ma.id = pd.marca_id
            LEFT JOIN modelo mo   ON mo.id = pd.modelo_id
            WHERE pd.pedidos_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        if ($r->marca_id  === '' || $r->marca_id  === 'null') $r->merge(['marca_id'  => null]);
        if ($r->modelo_id === '' || $r->modelo_id === 'null') $r->merge(['modelo_id' => null]);
        if ($r->deposito_id === '' || $r->deposito_id === 'null') $r->merge(['deposito_id' => null]);

        $r->validate([
            'pedidos_id'    => 'required|integer',
            'item_id'       => 'required|integer',
            'deposito_id'   => 'nullable|integer|exists:deposito,id',
            'det_cantidad'  => 'required|integer|min:1',
            'cantidad_stock'=> 'required|integer|min:0',
            'marca_id'      => 'nullable|integer|exists:marca,id',
            'modelo_id'     => 'nullable|integer|exists:modelo,id',
        ]);

        DB::table('pedidos_detalles')->insert([
            'pedidos_id'    => $r->pedidos_id,
            'item_id'       => $r->item_id,
            'deposito_id'   => $r->deposito_id ?: null,
            'det_cantidad'  => $r->det_cantidad,
            'cantidad_stock'=> $r->cantidad_stock,
            'marca_id'      => $r->marca_id ?: null,
            'modelo_id'     => $r->modelo_id ?: null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'mensaje' => 'Detalle creado con éxito',
            'tipo'    => 'success',
        ]);
    }

    public function update(Request $r, $pedidos_id)
    {
        if ($r->marca_id   === '' || $r->marca_id   === 'null') $r->merge(['marca_id'   => null]);
        if ($r->modelo_id  === '' || $r->modelo_id  === 'null') $r->merge(['modelo_id'  => null]);
        if ($r->deposito_id === '' || $r->deposito_id === 'null') $r->merge(['deposito_id' => null]);

        $r->validate([
            'item_id'       => 'required|integer',
            'deposito_id'   => 'nullable|integer|exists:deposito,id',
            'det_cantidad'  => 'required|integer|min:1',
            'cantidad_stock'=> 'required|integer|min:0',
            'marca_id'      => 'nullable|integer|exists:marca,id',
            'modelo_id'     => 'nullable|integer|exists:modelo,id',
        ]);

        DB::table('pedidos_detalles')
            ->where('pedidos_id', $pedidos_id)
            ->where('item_id', $r->item_id)
            ->update([
                'deposito_id'   => $r->deposito_id ?: null,
                'det_cantidad'  => intval($r->det_cantidad),
                'cantidad_stock'=> intval($r->cantidad_stock),
                'marca_id'      => $r->marca_id ?: null,
                'modelo_id'     => $r->modelo_id ?: null,
                'updated_at'    => now(),
            ]);

        return response()->json([
            'mensaje' => 'Detalle modificado correctamente',
            'tipo'    => 'success',
        ]);
    }

    public function destroy($pedidos_id, $item_id)
    {
        DB::table('pedidos_detalles')
            ->where('pedidos_id', $pedidos_id)
            ->where('item_id', $item_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success',
        ]);
    }
}
