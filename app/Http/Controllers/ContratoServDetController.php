<?php

namespace App\Http\Controllers;

use App\Models\ContratoServDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ContratoServDetController extends Controller
{
    public function read($id)
{
    return DB::select("
       SELECT 
            csd.contrato_serv_cab_id, 	
            csd.item_id, 
            csd.contrato_serv_det_cantidad, 
            csd.contrato_serv_det_costo,
            csd.contrato_serv_det_cantidad_stock,
            csd.tipo_impuesto_id,
            i.item_decripcion,
            ti.tip_imp_nom
        FROM contrato_serv_det csd
        JOIN items i ON i.id = csd.item_id
        join tipo_impuesto ti on ti.id = csd.tipo_impuesto_id 
        WHERE csd.contrato_serv_cab_id = ?
    ", [$id]);
}
    public function store(Request $r) {
    $data = $r->validate([
        'contrato_serv_cab_id' => 'required',
        'item_id' => 'required',
        'tipo_impuesto_id' => 'required',
        'contrato_serv_det_cantidad' => 'required|integer',
        'contrato_serv_det_costo' => 'required|integer',
        'contrato_serv_det_cantidad_stock' => 'required|integer',  // Asegúrate de validar cantidad_stock
    ]);

    // Ahora puedes guardar el detalle en la base de datos
    $detalle = new ContratoServDet();
    $detalle->contrato_serv_cab_id = $data['contrato_serv_cab_id'];
    $detalle->item_id = $data['item_id'];
    $detalle->tipo_impuesto_id = $data['tipo_impuesto_id'];
    $detalle->contrato_serv_det_cantidad = $data['contrato_serv_det_cantidad']; 
    $detalle->contrato_serv_det_costo = $data['contrato_serv_det_costo'];
    $detalle->contrato_serv_det_cantidad_stock = $data['contrato_serv_det_cantidad_stock']; 
    $detalle->save();

    return response()->json([
        'mensaje' => 'Detalle creado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ]);
}
public function update(Request $r, $contrato_serv_cab_id)
{
    DB::table('contrato_serv_det')
        ->where('contrato_serv_cab_id', $r->contrato_serv_cab_id)
        ->update([
            'item_id' => $r->item_id,
            'tipo_impuesto_id' => intval($r->tipo_impuesto_id),
            'contrato_serv_det_cantidad' => intval($r->contrato_serv_det_cantidad),
            'contrato_serv_det_costo' => intval($r->contrato_serv_det_costo),
            'contrato_serv_det_cantidad_stock' => intval($r->contrato_serv_det_cantidad_stock),
        ]);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success'
    ], 200);
}
public function destroy($contrato_serv_cab_id, $item_id)
{
    // Eliminar el detalle
    $detalle = DB::table('contrato_serv_det')
        ->where('contrato_serv_cab_id', $contrato_serv_cab_id)
        ->where('item_id', $item_id)
        ->delete();

    return response()->json([
        'mensaje' => 'Registro eliminado con éxito',
        'tipo' => 'success'
    ], 200);
}
}
