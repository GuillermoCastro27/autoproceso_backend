<?php

namespace App\Http\Controllers;

use App\Models\PresupuestoServDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PresupuestoServDetController extends Controller
{
    public function read($id)
{
    return DB::select("
        SELECT 
            psd.presupuesto_serv_cab_id,
            psd.item_id,
            psd.pres_serv_det_cantidad,
            psd.pres_serv_det_costo,
            psd.pres_serv_det_cantidad_stock,
            psd.tipo_impuesto_id,
            i.item_decripcion,
            ti.tip_imp_nom,
            tp.tipo_prom_modo,
            tp.tipo_prom_valor,
            dc.desc_cab_porcentaje
        FROM presupuesto_serv_det psd
        JOIN items i ON i.id = psd.item_id
        JOIN tipo_impuesto ti ON ti.id = psd.tipo_impuesto_id
        LEFT JOIN presupuesto_serv_cab psc ON psc.id = psd.presupuesto_serv_cab_id
        LEFT JOIN promociones_cab pc ON pc.id = psc.promociones_cab_id
        LEFT JOIN tipo_promociones tp ON tp.id = pc.tipo_promociones_id
        LEFT JOIN descuentos_cab dc ON dc.id = psc.descuentos_cab_id
        WHERE psd.presupuesto_serv_cab_id = ?
    ", [$id]);
}
public function store(Request $r) {
    $data = $r->validate([
        'presupuesto_serv_cab_id' => 'required',
        'item_id' => 'required',
        'tipo_impuesto_id' => 'required',
        'pres_serv_det_cantidad' => 'required|integer',
        'pres_serv_det_costo' => 'required|integer',
        'pres_serv_det_cantidad_stock' => 'required|integer',  // Asegúrate de validar cantidad_stock
    ]);

    // Ahora puedes guardar el detalle en la base de datos
    $detalle = new PresupuestoServDet();
    $detalle->presupuesto_serv_cab_id = $data['presupuesto_serv_cab_id'];
    $detalle->item_id = $data['item_id'];
    $detalle->tipo_impuesto_id = $data['tipo_impuesto_id'];
    $detalle->pres_serv_det_cantidad = $data['pres_serv_det_cantidad']; 
    $detalle->pres_serv_det_costo = $data['pres_serv_det_costo'];
    $detalle->pres_serv_det_cantidad_stock = $data['pres_serv_det_cantidad_stock']; 
    $detalle->save();

    return response()->json([
        'mensaje' => 'Detalle creado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ]);
}
public function update(Request $r, $presupuesto_serv_cab_id)
{
    DB::table('presupuesto_serv_det')
        ->where('presupuesto_serv_cab_id', $r->presupuesto_serv_cab_id)
        ->update([
            'item_id' => $r->item_id,
            'tipo_impuesto_id' => intval($r->tipo_impuesto_id),
            'pres_serv_det_cantidad' => intval($r->pres_serv_det_cantidad),
            'pres_serv_det_costo' => intval($r->pres_serv_det_costo),
            'pres_serv_det_cantidad_stock' => intval($r->pres_serv_det_cantidad_stock),
        ]);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success'
    ], 200);
}
public function destroy($presupuesto_serv_cab_id, $item_id)
{
    // Eliminar el detalle
    $detalle = DB::table('presupuesto_serv_det')
        ->where('presupuesto_serv_cab_id', $presupuesto_serv_cab_id)
        ->where('item_id', $item_id)
        ->delete();

    return response()->json([
        'mensaje' => 'Registro eliminado con éxito',
        'tipo' => 'success'
    ], 200);
}
}
