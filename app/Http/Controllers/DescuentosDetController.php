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
            i.item_decripcion
        FROM descuentos_det dd 
        JOIN items i ON i.id = dd.item_id
        WHERE dd.descuentos_cab_id = ?
    ", [$id]);
}
public function store(Request $r) {
    $data = $r->validate([
        'descuentos_cab_id' => 'required',
        'item_id' => 'required'
    ]);

    // Ahora puedes guardar el detalle en la base de datos
    $detalle = new DescuentosDet();
    $detalle->descuentos_cab_id = $data['descuentos_cab_id'];
    $detalle->item_id = $data['item_id']; 
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
