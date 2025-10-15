<?php

namespace App\Http\Controllers;
use App\Models\RecepcionDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class RecepcionDetController extends Controller
{
    public function read($id)
{
    return DB::select("
       SELECT 
            rd.recep_cab_id, 	
            rd.item_id, 
            rd.recep_det_cantidad, 
            rd.recep_det_costo,
            rd.recep_det_cantidad_stock,
            rd.tipo_impuesto_id,
            i.item_decripcion,
            ti.tip_imp_nom
        FROM recep_det rd 
        JOIN items i ON i.id = rd.item_id
        join tipo_impuesto ti on ti.id = rd.tipo_impuesto_id 
        WHERE rd.recep_cab_id = ?
    ", [$id]);
}
    public function store(Request $r) {
    $data = $r->validate([
        'recep_cab_id' => 'required',
        'item_id' => 'required',
        'tipo_impuesto_id' => 'required',
        'recep_det_cantidad' => 'required|integer',
        'recep_det_costo' => 'required|integer',
        'recep_det_cantidad_stock' => 'required|integer',  // Asegúrate de validar cantidad_stock
    ]);

    // Ahora puedes guardar el detalle en la base de datos
    $detalle = new RecepcionDet();
    $detalle->recep_cab_id = $data['recep_cab_id'];
    $detalle->item_id = $data['item_id'];
    $detalle->tipo_impuesto_id = $data['tipo_impuesto_id'];
    $detalle->recep_det_cantidad = $data['recep_det_cantidad']; 
    $detalle->recep_det_costo = $data['recep_det_costo'];
    $detalle->recep_det_cantidad_stock = $data['recep_det_cantidad_stock']; 
    $detalle->save();

    return response()->json([
        'mensaje' => 'Detalle creado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ]);
}
public function update(Request $r, $recep_cab_id)
{
    DB::table('recep_det')
        ->where('recep_cab_id', $r->recep_cab_id)
        ->update([
            'item_id' => $r->item_id,
            'tipo_impuesto_id' => intval($r->tipo_impuesto_id),
            'recep_det_cantidad' => intval($r->recep_det_cantidad),
            'recep_det_costo' => intval($r->recep_det_costo),
            'recep_det_cantidad_stock' => intval($r->recep_det_cantidad_stock),
        ]);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success'
    ], 200);
}
public function destroy($recep_cab_id, $item_id)
{
    // Eliminar el detalle
    $detalle = DB::table('recep_det')
        ->where('recep_cab_id', $recep_cab_id)
        ->where('item_id', $item_id)
        ->delete();

    return response()->json([
        'mensaje' => 'Registro eliminado con éxito',
        'tipo' => 'success'
    ], 200);
}
}
