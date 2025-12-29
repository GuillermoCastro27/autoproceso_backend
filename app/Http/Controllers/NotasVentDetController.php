<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotasVentDet;
use Illuminate\Support\Facades\DB;

class NotasVentDetController extends Controller
{
    /* ===============================
     * 📌 READ
     * =============================== */
    public function read($id)
    {
        return DB::select("
            SELECT 
                nvd.*,
                i.item_decripcion,
                i.item_precio,
                ti.tip_imp_nom
            FROM notas_vent_det nvd
            JOIN items i ON i.id = nvd.item_id
            JOIN tipo_impuesto ti ON ti.id = nvd.tipo_impuesto_id
            WHERE nvd.notas_vent_cab_id = ?
        ", [$id]);
    }
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'notas_vent_cab_id'        => 'required|exists:notas_vent_cab,id',
            'item_id'                 => 'required|exists:items,id',
            'tipo_impuesto_id'        => 'required|exists:tipo_impuesto,id',
            'notas_vent_det_cantidad' => 'required|numeric|min:0.01',
            'notas_vent_det_precio'   => 'required|numeric|min:0'
        ]);

        $detalle = NotasVentDet::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Detalle de nota de venta creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 201);
    }

    /* ===============================
     * 📌 UPDATE (PK COMPUESTA)
     * =============================== */
    public function update(Request $request, $notas_vent_cab_id, $item_id)
    {
        $datosValidados = $request->validate([
            'notas_vent_det_cantidad' => 'required|numeric|min:0.01',
            'tipo_impuesto_id'        => 'required|exists:tipo_impuesto,id',
            'notas_vent_det_precio'   => 'required|numeric|min:0'
        ]);

        $actualizado = DB::table('notas_vent_det')
            ->where('notas_vent_cab_id', $notas_vent_cab_id)
            ->where('item_id', $item_id)
            ->update(array_merge($datosValidados, [
                'updated_at' => now()
            ]));

        if ($actualizado === 0) {
            return response()->json([
                'mensaje' => 'Detalle de nota de venta no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Detalle de nota de venta modificado con éxito',
            'tipo'    => 'success'
        ], 200);
    }

    /* ===============================
     * 📌 DESTROY
     * =============================== */
    public function destroy($notas_vent_cab_id, $item_id)
    {
        $deleted = NotasVentDet::where('notas_vent_cab_id', $notas_vent_cab_id)
            ->where('item_id', $item_id)
            ->delete();

        if ($deleted) {
            return response()->json([
                'mensaje' => 'Detalle de nota de venta eliminado con éxito',
                'tipo'    => 'success'
            ], 200);
        }

        return response()->json([
            'mensaje' => 'Detalle de nota de venta no encontrado',
            'tipo'    => 'error'
        ], 404);
    }
}