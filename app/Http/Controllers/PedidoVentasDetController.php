<?php

namespace App\Http\Controllers;

use App\Models\PedidoVentasDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoVentasDetController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT
                pvd.pedidos_ventas_id,
                pvd.item_id,
                pvd.det_cantidad,
                pvd.cantidad_stock,
                pvd.deposito_id,
                pvd.marca_id,
                pvd.modelo_id,
                i.item_decripcion,
                d.dep_nombre,
                ma.marc_nom,
                mo.modelo_nom,
                mo.modelo_año
            FROM pedidos_ventas_det pvd
            JOIN items i ON i.id = pvd.item_id
            LEFT JOIN deposito d ON d.id = pvd.deposito_id
            LEFT JOIN marca ma ON ma.id = pvd.marca_id
            LEFT JOIN modelo mo ON mo.id = pvd.modelo_id
            WHERE pvd.pedidos_ventas_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'pedidos_ventas_id' => 'required',
            'item_id'           => 'required',
            'det_cantidad'      => 'required|integer',
            'cantidad_stock'    => 'required|integer',
            'deposito_id'       => 'nullable|integer|exists:deposito,id',
            'marca_id'          => 'nullable|integer|exists:marca,id',
            'modelo_id'         => 'nullable|integer|exists:modelo,id',
        ]);

        $detalle = new PedidoVentasDet();
        $detalle->pedidos_ventas_id = $data['pedidos_ventas_id'];
        $detalle->item_id           = $data['item_id'];
        $detalle->det_cantidad      = $data['det_cantidad'];
        $detalle->cantidad_stock    = $data['cantidad_stock'];
        $detalle->deposito_id       = $data['deposito_id'] ?? null;
        $detalle->marca_id          = $data['marca_id']    ?? null;
        $detalle->modelo_id         = $data['modelo_id']   ?? null;
        $detalle->save();

        return response()->json([
            'mensaje'  => 'Detalle creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 200);
    }

    public function update(Request $r, $pedidos_ventas_id)
    {
        DB::table('pedidos_ventas_det')
            ->where('pedidos_ventas_id', $pedidos_ventas_id)
            ->where('item_id', $r->item_id)
            ->update([
                'det_cantidad'   => intval($r->det_cantidad),
                'cantidad_stock' => intval($r->cantidad_stock),
                'deposito_id'    => $r->deposito_id ?: null,
                'marca_id'       => $r->marca_id    ? intval($r->marca_id)  : null,
                'modelo_id'      => $r->modelo_id   ? intval($r->modelo_id) : null,
            ]);

        $detalle = DB::select("
            SELECT *
            FROM pedidos_ventas_det
            WHERE pedidos_ventas_id = ?
              AND item_id = ?
        ", [$pedidos_ventas_id, $r->item_id]);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 200);
    }

    public function destroy($pedidos_ventas_id, $item_id)
    {
        DB::table('pedidos_ventas_det')
            ->where('pedidos_ventas_id', $pedidos_ventas_id)
            ->where('item_id', $item_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }
}
