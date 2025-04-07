<?php

namespace App\Http\Controllers;

use App\Models\AjusteDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AjusteDetController extends Controller
{
    public function read($id)
{
    return DB::select("
        SELECT 
            ad.ajuste_cab_id, 
            ad.item_id, 
            ad.ajus_det_cantidad, 
            ad.cantidad_stock, 
            i.item_decripcion
        FROM ajuste_det ad
        JOIN items i ON i.id = ad.item_id
        WHERE ad.ajuste_cab_id = ?
    ", [$id]);
}
public function store(Request $r){
    $datosValidados = $r->validate([
        'ajuste_cab_id' => 'required',
        'item_id' => 'required',
        'ajus_det_cantidad' => 'required|integer',
        'cantidad_stock' => 'required|integer'
    ]);
    $detalle = AjusteDet::create($datosValidados);
    return response()->json([
        'mensaje'=>'Registro creado con exito',
        'tipo'=>'success',
        'registro'=> $detalle
    ],200);
}
public function update(Request $r, $ajuste_cab_id) {
    // Actualizar el registro
    DB::table('ajuste_det') // Cambio de 'pedidos-detalles' a 'ajuste_det'
        ->where('ajuste_cab_id', $ajuste_cab_id)
        ->update([
            'item_id' => $r->item_id,
            'ajus_det_cantidad' => intval($r->ajus_det_cantidad),
            'cantidad_stock' => intval($r->cantidad_stock)
        ]);

    // Definir item_id correctamente
    $item_id = $r->item_id;

    // Consultar el registro actualizado usando parámetros enlazados
    $detalle = DB::select("SELECT * FROM ajuste_det WHERE ajuste_cab_id = ? AND item_id = ?", [$ajuste_cab_id, $item_id]);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ], 200);
}
public function destroy($ajuste_cab_id, $item_id)
{
    // Eliminar el detalle
    $detalle = DB::table('ajuste_det')
        ->where('ajuste_cab_id', $ajuste_cab_id)
        ->where('item_id', $item_id)
        ->delete();

    return response()->json([
        'mensaje' => 'Registro eliminado con éxito',
        'tipo' => 'success'
    ], 200);
}
}
