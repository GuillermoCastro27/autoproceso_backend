<?php

namespace App\Http\Controllers;

use App\Models\DescuentosDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DescuentosDetController extends Controller
{
    public function read($id)
{
    return DB::select("
       SELECT 
            dd.descuentos_cab_id, 	
            dd.item_id, 
            dd.desc_det_cantidad, 
            dd.desc_det_costo,
            dd.tipo_impuesto_id,
            i.item_decripcion,
            ti.tip_imp_nom
        FROM descuentos_det dd 
        JOIN items i ON i.id = dd.item_id
        JOIN tipo_impuesto ti ON ti.id = dd.tipo_impuesto_id
        WHERE dd.descuentos_cab_id = ?
    ", [$id]);
}
public function store(Request $r) {
    $data = $r->validate([
        'descuentos_cab_id' => 'required',
        'item_id' => 'required',
        'tipo_impuesto_id' => 'required',
        'desc_det_cantidad' => 'required',
        'desc_det_costo' => 'required'
    ]);

    // Ahora puedes guardar el detalle en la base de datos
    $detalle = new DescuentosDet();
    $detalle->descuentos_cab_id = $data['descuentos_cab_id'];
    $detalle->item_id = $data['item_id']; 
    $detalle->tipo_impuesto_id = $data['tipo_impuesto_id'];
    $detalle->desc_det_cantidad = $data['desc_det_cantidad'];
    $detalle->desc_det_costo = $data['desc_det_costo'];
    $detalle->save();

    return response()->json([
        'mensaje' => 'Detalle creado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ]);
}
public function update(Request $r, $descuentos_cab_id)
{
    DB::table('descuentos_det')
        ->where('descuentos_cab_id', $r->descuentos_cab_id)
        ->update([
            'item_id' => $r->item_id,
            'tipo_impuesto_id' => $r->tipo_impuesto_id,
            'desc_det_cantidad' => intval($r->desc_det_cantidad),
            'desc_det_costo' => intval($r->desc_det_costo),
        ]);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success'
    ], 200);
}
public function destroy($descuentos_cab_id, $item_id)
{
    // Eliminar el detalle
    $detalle = DB::table('descuentos_det')
        ->where('descuentos_cab_id', $descuentos_cab_id)
        ->where('item_id', $item_id)
        ->delete();

    return response()->json([
        'mensaje' => 'Registro eliminado con éxito',
        'tipo' => 'success'
    ], 200);
}
}
