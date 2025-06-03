<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotaCompDet;
use Illuminate\Support\Facades\DB;

class NotasComdetController extends Controller
{
    public function read($id) {
        return DB::select("
            select 
                ncd.*, 
                i.item_decripcion, 
                i.item_costo,  
                ti.tip_imp_nom
            from notas_comp_det ncd
            join items i on i.id = ncd.item_id
            join tipo_impuesto ti on ti.id = ncd.tipo_impuesto_id
            WHERE ncd.notas_comp_cab_id = $id");
    }
    public function store(Request $request)
{
    $datosValidados = $request->validate([
        'notas_comp_cab_id' => 'required|exists:notas_comp_cab,id',
        'item_id' => 'required|exists:items,id',
        'tipo_impuesto_id' => 'required|exists:tipo_impuesto,id',
        'notas_comp_det_cantidad' => 'required|numeric',
        'notas_comp_det_costo' => 'required|numeric',
    ]);

    // Crear el detalle de la nota de compra
    $detalle = NotaCompDet::create($datosValidados);

    return response()->json([
        'mensaje' => 'Registro creado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ], 201);
}

public function update(Request $request, $notas_comp_cab_id, $item_id)
{
    $datosValidados = $request->validate([
        'notas_comp_det_cantidad' => 'required|numeric',
        'tipo_impuesto_id' => 'required|exists:tipo_impuesto,id',
        'notas_comp_det_costo' => 'required|numeric',
    ]);

    $detalle = NotaCompDet::where('notas_comp_cab_id', $notas_comp_cab_id)
        ->where('item_id', $item_id)
        ->first();

    if (!$detalle) {
        return response()->json([
            'mensaje' => 'Detalle no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    $detalle->update($datosValidados);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ], 200);
}

public function destroy($notas_comp_cab_id, $item_id)
{
    $deleted = NotaCompDet::where('notas_comp_cab_id', $notas_comp_cab_id)
        ->where('item_id', $item_id)
        ->delete();

    if ($deleted) {
        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo' => 'success',
        ], 200);
    } else {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error',
        ], 404);
    }
}

}
