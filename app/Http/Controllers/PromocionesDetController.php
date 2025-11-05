<?php

namespace App\Http\Controllers;
use App\Models\PromocionesDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PromocionesDetController extends Controller
{
    public function read($id)
{
    return DB::select("
       SELECT 
            pd.promociones_cab_id, 	
            pd.item_id, 
            pd.prom_det_cantidad, 
            pd.prom_det_costo,
            pd.tipo_impuesto_id,
            i.item_decripcion,
            ti.tip_imp_nom
        FROM promociones_det pd 
        JOIN items i ON i.id = pd.item_id
        JOIN tipo_impuesto ti ON ti.id = pd.tipo_impuesto_id
        WHERE pd.promociones_cab_id = ?
    ", [$id]);
}
public function store(Request $r) {
    $data = $r->validate([
        'promociones_cab_id' => 'required',
        'item_id' => 'required',
        'tipo_impuesto_id' => 'required',
        'prom_det_cantidad' => 'required',
        'prom_det_costo' => 'required'
    ]);

    // Ahora puedes guardar el detalle en la base de datos
    $detalle = new PromocionesDet();
    $detalle->promociones_cab_id = $data['promociones_cab_id'];
    $detalle->item_id = $data['item_id'];
    $detalle->tipo_impuesto_id = $data['tipo_impuesto_id'];
    $detalle->prom_det_cantidad = $data['prom_det_cantidad'];
    $detalle->prom_det_costo = $data['prom_det_costo'];
    $detalle->save();

    return response()->json([
        'mensaje' => 'Detalle creado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ]);
}
public function update(Request $r, $promociones_cab_id)
{
    DB::table('promociones_det')
        ->where('promociones_cab_id', $r->promociones_cab_id)
        ->update([
            'item_id' => $r->item_id,
            'tipo_impuesto_id' => $r->tipo_impuesto_id,
            'prom_det_cantidad' => intval($r->prom_det_cantidad),
            'prom_det_costo' => intval($r->prom_det_costo),
        ]);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success'
    ], 200);
}
public function destroy($promociones_cab_id, $item_id)
{
    // Eliminar el detalle
    $detalle = DB::table('promociones_det')
        ->where('promociones_cab_id', $promociones_cab_id)
        ->where('item_id', $item_id)
        ->delete();

    return response()->json([
        'mensaje' => 'Registro eliminado con éxito',
        'tipo' => 'success'
    ], 200);
}
}
