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
                i.item_decripcion
            FROM pedidos_ventas_det pvd
            JOIN items i ON i.id = pvd.item_id
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
        ]);

        $detalle = new PedidoVentasDet();
        $detalle->pedidos_ventas_id = $data['pedidos_ventas_id'];
        $detalle->item_id           = $data['item_id'];
        $detalle->det_cantidad      = $data['det_cantidad'];
        $detalle->cantidad_stock    = $data['cantidad_stock'];
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
                'cantidad_stock' => intval($r->cantidad_stock)
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
