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
                i.item_decripcion,
                d.dep_nombre
            FROM pedidos_detalles pd
            JOIN items i ON i.id = pd.item_id
            LEFT JOIN deposito d ON d.id = pd.deposito_id
            WHERE pd.pedidos_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'pedidos_id'    => 'required|integer',
            'item_id'       => 'required|integer',
            'deposito_id'   => 'nullable|integer|exists:deposito,id',
            'det_cantidad'  => 'required|integer|min:1',
            'cantidad_stock'=> 'required|integer|min:0',
        ]);

        $detalle = new PedidosDetalle();
        $detalle->pedidos_id    = $data['pedidos_id'];
        $detalle->item_id       = $data['item_id'];
        $detalle->deposito_id   = $data['deposito_id'] ?? null;
        $detalle->det_cantidad  = $data['det_cantidad'];
        $detalle->cantidad_stock= $data['cantidad_stock'];
        $detalle->save();

        return response()->json([
            'mensaje' => 'Detalle creado con éxito',
            'tipo' => 'success',
            'registro' => $detalle
        ]);
    }

    public function update(Request $r, $pedidos_id)
    {
        $r->validate([
            'item_id'       => 'required|integer',
            'deposito_id'   => 'nullable|integer|exists:deposito,id',
            'det_cantidad'  => 'required|integer|min:1',
            'cantidad_stock'=> 'required|integer|min:0'
        ]);

        $detalle = PedidosDetalle::where('pedidos_id', $pedidos_id)
            ->where('item_id', $r->item_id)
            ->first();

        if (!$detalle) {
            return response()->json([
                'mensaje' => 'Detalle no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $detalle->deposito_id   = $r->deposito_id ?? null;
        $detalle->det_cantidad  = intval($r->det_cantidad);
        $detalle->cantidad_stock= intval($r->cantidad_stock);
        $detalle->save();

        return response()->json([
            'mensaje' => 'Cantidad modificada correctamente',
            'tipo' => 'success',
            'registro' => $detalle
        ], 200);
    }

    public function destroy($pedidos_id, $item_id)
    {
        $detalle = PedidosDetalle::where('pedidos_id', $pedidos_id)
            ->where('item_id', $item_id)
            ->first();

        if (!$detalle) {
            return response()->json([
                'mensaje' => 'Detalle no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $detalle->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo' => 'success'
        ], 200);
    }
}