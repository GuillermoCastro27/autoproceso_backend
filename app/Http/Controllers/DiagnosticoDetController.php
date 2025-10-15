<?php

namespace App\Http\Controllers;
use App\Models\DiagnosticoDet;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class DiagnosticoDetController extends Controller
{
    public function read($id)
{
    return DB::select("
       SELECT 
            dd.diagnostico_cab_id, 	
            dd.item_id, 
            dd.diag_det_cantidad, 
            dd.diag_det_costo,
            dd.diag_det_cantidad_stock,
            dd.tipo_impuesto_id,
            i.item_decripcion,
            ti.tip_imp_nom
        FROM diagnostico_det dd 
        JOIN items i ON i.id = dd.item_id
        join tipo_impuesto ti on ti.id = dd.tipo_impuesto_id 
        WHERE dd.diagnostico_cab_id = ?
    ", [$id]);
}
    public function store(Request $r) {
    $data = $r->validate([
        'diagnostico_cab_id' => 'required',
        'item_id' => 'required',
        'tipo_impuesto_id' => 'required',
        'diag_det_cantidad' => 'required|integer',
        'diag_det_costo' => 'required|integer',
        'diag_det_cantidad_stock' => 'required|integer',  // Asegúrate de validar cantidad_stock
    ]);

    // Ahora puedes guardar el detalle en la base de datos
    $detalle = new DiagnosticoDet();
    $detalle->diagnostico_cab_id = $data['diagnostico_cab_id'];
    $detalle->item_id = $data['item_id'];
    $detalle->tipo_impuesto_id = $data['tipo_impuesto_id'];
    $detalle->diag_det_cantidad = $data['diag_det_cantidad']; 
    $detalle->diag_det_costo = $data['diag_det_costo'];
    $detalle->diag_det_cantidad_stock = $data['diag_det_cantidad_stock']; 
    $detalle->save();

    return response()->json([
        'mensaje' => 'Detalle creado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ]);
}
public function update(Request $r, $diagnostico_cab_id)
{
    DB::table('diagnostico_det')
        ->where('diagnostico_cab_id', $r->diagnostico_cab_id)
        ->update([
            'item_id' => $r->item_id,
            'tipo_impuesto_id' => intval($r->tipo_impuesto_id),
            'diag_det_cantidad' => intval($r->diag_det_cantidad),
            'diag_det_costo' => intval($r->diag_det_costo),
            'diag_det_cantidad_stock' => intval($r->diag_det_cantidad_stock),
        ]);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success'
    ], 200);
}
public function destroy($diagnostico_cab_id, $item_id)
{
    // Eliminar el detalle
    $detalle = DB::table('diagnostico_det')
        ->where('diagnostico_cab_id', $diagnostico_cab_id)
        ->where('item_id', $item_id)
        ->delete();

    return response()->json([
        'mensaje' => 'Registro eliminado con éxito',
        'tipo' => 'success'
    ], 200);
}
}
