<?php

namespace App\Http\Controllers;
use App\Models\SolicitudDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class SolicitudDetController extends Controller
{
    public function read($id)
{
    return DB::select("
       SELECT 
            sd.solicitudes_cab_id, 	
            sd.item_id, 
            sd.soli_det_cantidad, 
            sd.soli_det_costo,
            sd.soli_det_cantidad_stock,
            sd.tipo_impuesto_id,
            i.item_decripcion,
            ti.tip_imp_nom
        FROM solicitudes_det sd
        JOIN items i ON i.id = sd.item_id
        join tipo_impuesto ti on ti.id = sd.tipo_impuesto_id 
        WHERE sd.solicitudes_cab_id = ?
    ", [$id]);
}
public function store(Request $r) {
    $data = $r->validate([
        'solicitudes_cab_id' => 'required',
        'item_id' => 'required',
        'tipo_impuesto_id' => 'required',
        'soli_det_cantidad' => 'required|integer',
        'soli_det_costo' => 'required|integer',
        'soli_det_cantidad_stock' => 'required|integer',  // Asegúrate de validar cantidad_stock
    ]);

    // Ahora puedes guardar el detalle en la base de datos
    $detalle = new SolicitudDet();
    $detalle->solicitudes_cab_id = $data['solicitudes_cab_id'];
    $detalle->item_id = $data['item_id'];
    $detalle->tipo_impuesto_id = $data['tipo_impuesto_id'];
    $detalle->soli_det_cantidad = $data['soli_det_cantidad']; 
    $detalle->soli_det_costo = $data['soli_det_costo'];
    $detalle->soli_det_cantidad_stock = $data['soli_det_cantidad_stock']; 
    $detalle->save();

    return response()->json([
        'mensaje' => 'Detalle creado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ]);
}
public function update(Request $r, $solicitudes_cab_id)
{
    DB::table('solicitudes_det')
        ->where('solicitudes_cab_id', $r->solicitudes_cab_id)
        ->update([
            'item_id' => $r->item_id,
            'tipo_impuesto_id' => intval($r->tipo_impuesto_id),
            'soli_det_cantidad' => intval($r->soli_det_cantidad),
            'soli_det_costo' => intval($r->soli_det_costo),
            'soli_det_cantidad_stock' => intval($r->soli_det_cantidad_stock),
        ]);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success'
    ], 200);
}
public function destroy($solicitudes_cab_id, $item_id)
{
    // Eliminar el detalle
    $detalle = DB::table('solicitudes_det')
        ->where('solicitudes_cab_id', $solicitudes_cab_id)
        ->where('item_id', $item_id)
        ->delete();

    return response()->json([
        'mensaje' => 'Registro eliminado con éxito',
        'tipo' => 'success'
    ], 200);
}
}
