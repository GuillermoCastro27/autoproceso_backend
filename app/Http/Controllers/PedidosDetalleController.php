<?php

namespace App\Http\Controllers;

use App\Models\PedidosDetalle;
use Illuminate\Support\Facades\Log;
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
            pd.det_cantidad, 
            pd.cantidad_stock,  -- Mostramos la cantidad de stock almacenada en pedidos_detalles
            i.item_decripcion
        FROM pedidos_detalles pd
        JOIN items i ON i.id = pd.item_id
        WHERE pd.pedidos_id = ?
    ", [$id]);
}

public function store(Request $r) {
    $data = $r->validate([
        'pedidos_id' => 'required',
        'item_id' => 'required',
        'det_cantidad' => 'required|integer',
        'cantidad_stock' => 'required|integer',  // Asegúrate de validar cantidad_stock
    ]);

    // Ahora puedes guardar el detalle en la base de datos
    $detalle = new PedidosDetalle();
    $detalle->pedidos_id = $data['pedidos_id'];
    $detalle->item_id = $data['item_id'];
    $detalle->det_cantidad = $data['det_cantidad'];
    $detalle->cantidad_stock = $data['cantidad_stock'];  // Guardamos la cantidad de stock
    $detalle->save();

    return response()->json([
        'mensaje' => 'Detalle creado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ]);
}

public function update(Request $r, $pedidos_id) {
    // Actualizar el registro
    DB::table('pedidos_detalles') // Cambio de 'pedidos-detalles' a 'pedidos_detalles'
        ->where('pedidos_id', $pedidos_id)
        ->update([
            'item_id' => $r->item_id,
            'det_cantidad' => intval($r->det_cantidad),
            'cantidad_stock' => intval($r->cantidad_stock)
        ]);

    // Definir item_id correctamente
    $item_id = $r->item_id;

    // Consultar el registro actualizado usando parámetros enlazados
    $detalle = DB::select("SELECT * FROM pedidos_detalles WHERE pedidos_id = ? AND item_id = ?", [$pedidos_id, $item_id]);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ], 200);
}


public function destroy($pedidos_id, $item_id)
{
    // Eliminar el detalle
    $detalle = DB::table('pedidos_detalles')
        ->where('pedidos_id', $pedidos_id)
        ->where('item_id', $item_id)
        ->delete();

    return response()->json([
        'mensaje' => 'Registro eliminado con éxito',
        'tipo' => 'success'
    ], 200);
}
}
