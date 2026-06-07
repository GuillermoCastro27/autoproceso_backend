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
                dd.marca_id,
                dd.modelo_id,
                i.item_decripcion,
                ti.tip_imp_nom,
                COALESCE(m.marc_nom, '')   AS marc_nom,
                COALESCE(mo.modelo_nom, '') AS modelo_nom
            FROM descuentos_det dd
            JOIN items i          ON i.id  = dd.item_id
            JOIN tipo_impuesto ti  ON ti.id = dd.tipo_impuesto_id
            LEFT JOIN marca m      ON m.id  = dd.marca_id
            LEFT JOIN modelo mo    ON mo.id = dd.modelo_id
            WHERE dd.descuentos_cab_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'descuentos_cab_id' => 'required|integer|exists:descuentos_cab,id',
            'item_id'           => 'required|integer|exists:items,id',
            'tipo_impuesto_id'  => 'required|integer|exists:tipo_impuesto,id',
            'desc_det_cantidad' => 'required|numeric|min:0.01',
            'desc_det_costo'    => 'required|numeric|min:0',
            'marca_id'          => 'nullable|integer|exists:marca,id',
            'modelo_id'         => 'nullable|integer|exists:modelo,id',
        ], [
            'descuentos_cab_id.required' => 'El descuento es obligatorio.',
            'item_id.required'           => 'Debe seleccionar un producto.',
            'item_id.exists'             => 'El producto seleccionado no es válido.',
            'tipo_impuesto_id.required'  => 'El tipo de impuesto es obligatorio.',
            'desc_det_cantidad.required' => 'La cantidad es obligatoria.',
            'desc_det_cantidad.min'      => 'La cantidad debe ser mayor a cero.',
            'desc_det_costo.required'    => 'El costo es obligatorio.',
            'desc_det_costo.min'         => 'El costo no puede ser negativo.',
        ]);

        $detalle = DescuentosDet::create([
            'descuentos_cab_id' => $r->descuentos_cab_id,
            'item_id'           => $r->item_id,
            'tipo_impuesto_id'  => $r->tipo_impuesto_id,
            'desc_det_cantidad' => $r->desc_det_cantidad,
            'desc_det_costo'    => $r->desc_det_costo,
            'marca_id'          => $r->marca_id  ?: null,
            'modelo_id'         => $r->modelo_id ?: null,
        ]);

        return response()->json([
            'mensaje'  => 'Detalle creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle,
        ], 200);
    }

    public function update(Request $r, $descuentos_cab_id)
    {
        $r->validate([
            'original_item_id'  => 'required|integer',
            'item_id'           => 'required|integer|exists:items,id',
            'tipo_impuesto_id'  => 'required|integer|exists:tipo_impuesto,id',
            'desc_det_cantidad' => 'required|numeric|min:0.01',
            'desc_det_costo'    => 'required|numeric|min:0',
            'marca_id'          => 'nullable|integer|exists:marca,id',
            'modelo_id'         => 'nullable|integer|exists:modelo,id',
        ], [
            'item_id.required'           => 'Debe seleccionar un producto.',
            'desc_det_cantidad.required' => 'La cantidad es obligatoria.',
            'desc_det_cantidad.min'      => 'La cantidad debe ser mayor a cero.',
            'desc_det_costo.required'    => 'El costo es obligatorio.',
            'desc_det_costo.min'         => 'El costo no puede ser negativo.',
        ]);

        DB::table('descuentos_det')
            ->where('descuentos_cab_id', $r->descuentos_cab_id)
            ->where('item_id', $r->original_item_id)
            ->update([
                'item_id'          => $r->item_id,
                'tipo_impuesto_id' => $r->tipo_impuesto_id,
                'desc_det_cantidad'=> $r->desc_det_cantidad,
                'desc_det_costo'   => $r->desc_det_costo,
                'marca_id'         => $r->marca_id  ?: null,
                'modelo_id'        => $r->modelo_id ?: null,
            ]);

        return response()->json([
            'mensaje' => 'Detalle modificado con éxito',
            'tipo'    => 'success',
        ], 200);
    }

    public function destroy($descuentos_cab_id, $item_id)
    {
        DB::table('descuentos_det')
            ->where('descuentos_cab_id', $descuentos_cab_id)
            ->where('item_id', $item_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Detalle eliminado con éxito',
            'tipo'    => 'success',
        ], 200);
    }
}
