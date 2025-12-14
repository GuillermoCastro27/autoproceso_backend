<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReclamoCliDet;
use Illuminate\Support\Facades\DB;

class ReclamoCliDetController extends Controller
{
     public function read($id)
{
    return DB::select("
       SELECT 
            rcd.reclamo_cli_cab_id, 	
            rcd.item_id, 
            rcd.rec_cli_det_cantidad, 
            rcd.rec_cli_det_costo,
            rcd.rec_cli_det_cantidad_stock,
            rcd.tipo_impuesto_id,
            i.item_decripcion,
            ti.tip_imp_nom
        FROM reclamo_cli_det rcd
        JOIN items i ON i.id = rcd.item_id
        join tipo_impuesto ti on ti.id = rcd.tipo_impuesto_id 
        WHERE rcd.reclamo_cli_cab_id = ?
    ", [$id]);
}
public function store(Request $r) {
    $data = $r->validate([
        'reclamo_cli_cab_id' => 'required',
        'item_id' => 'required',
        'tipo_impuesto_id' => 'required',
        'rec_cli_det_cantidad' => 'required|integer',
        'rec_cli_det_costo' => 'required|integer',
        'rec_cli_det_cantidad_stock' => 'required|integer',  // Asegúrate de validar cantidad_stock
    ]);

    // Ahora puedes guardar el detalle en la base de datos
    $detalle = new ReclamoCliDet();
    $detalle->reclamo_cli_cab_id = $data['reclamo_cli_cab_id'];
    $detalle->item_id = $data['item_id'];
    $detalle->tipo_impuesto_id = $data['tipo_impuesto_id'];
    $detalle->rec_cli_det_cantidad = $data['rec_cli_det_cantidad']; 
    $detalle->rec_cli_det_costo = $data['rec_cli_det_costo'];
    $detalle->rec_cli_det_cantidad_stock = $data['rec_cli_det_cantidad_stock']; 
    $detalle->save();

    return response()->json([
        'mensaje' => 'Detalle creado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ]);
}
public function update(Request $r, $reclamo_cli_cab_id)
{
    DB::table('reclamo_cli_det')
        ->where('reclamo_cli_cab_id', $r->reclamo_cli_cab_id)
        ->update([
            'item_id' => $r->item_id,
            'tipo_impuesto_id' => intval($r->tipo_impuesto_id),
            'rec_cli_det_cantidad' => intval($r->rec_cli_det_cantidad),
            'rec_cli_det_costo' => intval($r->rec_cli_det_costo),
            'rec_cli_det_cantidad_stock' => intval($r->rec_cli_det_cantidad_stock),
        ]);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success'
    ], 200);
}
public function destroy($reclamo_cli_cab_id, $item_id)
{
    // Eliminar el detalle
    $detalle = DB::table('reclamo_cli_det')
        ->where('reclamo_cli_cab_id', $reclamo_cli_cab_id)
        ->where('item_id', $item_id)
        ->delete();

    return response()->json([
        'mensaje' => 'Registro eliminado con éxito',
        'tipo' => 'success'
    ], 200);
}
}
