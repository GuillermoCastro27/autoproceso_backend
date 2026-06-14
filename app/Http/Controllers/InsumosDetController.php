<?php

namespace App\Http\Controllers;

use App\Models\InsumosDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsumosDetController extends Controller
{
    public function readByCab($cab_id)
    {
        return DB::select("
            SELECT
                d.id,
                d.insumos_cab_id,
                d.item_id,
                d.tipo_impuesto_id,
                d.ins_det_cantidad,
                d.ins_det_costo,
                d.marca_id,
                d.modelo_id,
                i.item_decripcion,
                ti.tip_imp_nom,
                COALESCE(m.marc_nom,  '') AS marc_nom,
                COALESCE(mo.modelo_nom,'') AS modelo_nom,
                ROUND((d.ins_det_cantidad * d.ins_det_costo)::numeric, 2) AS subtotal
            FROM insumos_det d
            JOIN items i          ON i.id  = d.item_id
            JOIN tipo_impuesto ti ON ti.id = d.tipo_impuesto_id
            LEFT JOIN marca m     ON m.id  = d.marca_id
            LEFT JOIN modelo mo   ON mo.id = d.modelo_id
            WHERE d.insumos_cab_id = ?
            ORDER BY d.id ASC
        ", [$cab_id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'insumos_cab_id'    => 'required|integer|exists:insumos_cab,id',
            'item_id'           => 'required|integer|exists:items,id',
            'tipo_impuesto_id'  => 'required|integer|exists:tipo_impuesto,id',
            'ins_det_cantidad'  => 'required|numeric|min:0.01',
            'ins_det_costo'     => 'required|numeric|min:0',
            'marca_id'          => 'nullable|integer',
            'modelo_id'         => 'nullable|integer',
        ]);

        $det = InsumosDet::create([
            'insumos_cab_id'   => $r->insumos_cab_id,
            'item_id'          => $r->item_id,
            'tipo_impuesto_id' => $r->tipo_impuesto_id,
            'ins_det_cantidad' => $r->ins_det_cantidad,
            'ins_det_costo'    => $r->ins_det_costo,
            'marca_id'         => $r->marca_id ?: null,
            'modelo_id'        => $r->modelo_id ?: null,
        ]);

        return response()->json([
            'mensaje'  => 'Ítem agregado correctamente.',
            'tipo'     => 'success',
            'registro' => $det,
        ], 201);
    }

    public function update(Request $r, $id)
    {
        $det = InsumosDet::find($id);
        if (!$det) {
            return response()->json(['mensaje' => 'Ítem no encontrado.', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'ins_det_cantidad' => 'required|numeric|min:0.01',
            'ins_det_costo'    => 'required|numeric|min:0',
            'marca_id'         => 'nullable|integer',
            'modelo_id'        => 'nullable|integer',
        ]);

        $det->update([
            'ins_det_cantidad' => $r->ins_det_cantidad,
            'ins_det_costo'    => $r->ins_det_costo,
            'marca_id'         => $r->marca_id ?: null,
            'modelo_id'        => $r->modelo_id ?: null,
        ]);

        return response()->json(['mensaje' => 'Ítem actualizado correctamente.', 'tipo' => 'success', 'registro' => $det]);
    }

    public function destroy($id)
    {
        $det = InsumosDet::find($id);
        if (!$det) {
            return response()->json(['mensaje' => 'Ítem no encontrado.', 'tipo' => 'error'], 404);
        }

        $det->delete();

        return response()->json(['mensaje' => 'Ítem eliminado correctamente.', 'tipo' => 'success']);
    }
}
