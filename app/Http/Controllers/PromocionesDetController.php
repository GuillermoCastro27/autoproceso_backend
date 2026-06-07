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
                pd.marca_id,
                pd.modelo_id,
                i.item_decripcion,
                ti.tip_imp_nom,
                COALESCE(m.marc_nom, '')  AS marc_nom,
                COALESCE(mo.modelo_nom, '') AS modelo_nom
            FROM promociones_det pd
            JOIN items i          ON i.id  = pd.item_id
            JOIN tipo_impuesto ti  ON ti.id = pd.tipo_impuesto_id
            LEFT JOIN marca m      ON m.id  = pd.marca_id
            LEFT JOIN modelo mo    ON mo.id = pd.modelo_id
            WHERE pd.promociones_cab_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'promociones_cab_id' => 'required|integer|exists:promociones_cab,id',
            'item_id'            => 'required|integer|exists:items,id',
            'tipo_impuesto_id'   => 'required|integer|exists:tipo_impuesto,id',
            'prom_det_cantidad'  => 'required|numeric|min:0.01',
            'prom_det_costo'     => 'required|numeric|min:0',
            'marca_id'           => 'nullable|integer|exists:marca,id',
            'modelo_id'          => 'nullable|integer|exists:modelo,id',
        ], [
            'promociones_cab_id.required' => 'La promoción es obligatoria.',
            'item_id.required'            => 'Debe seleccionar un producto.',
            'item_id.exists'              => 'El producto seleccionado no es válido.',
            'tipo_impuesto_id.required'   => 'El tipo de impuesto es obligatorio.',
            'prom_det_cantidad.required'  => 'La cantidad es obligatoria.',
            'prom_det_cantidad.min'       => 'La cantidad debe ser mayor a cero.',
            'prom_det_costo.required'     => 'El costo es obligatorio.',
            'prom_det_costo.min'          => 'El costo no puede ser negativo.',
        ]);

        $detalle = PromocionesDet::create([
            'promociones_cab_id' => $r->promociones_cab_id,
            'item_id'            => $r->item_id,
            'tipo_impuesto_id'   => $r->tipo_impuesto_id,
            'prom_det_cantidad'  => $r->prom_det_cantidad,
            'prom_det_costo'     => $r->prom_det_costo,
            'marca_id'           => $r->marca_id  ?: null,
            'modelo_id'          => $r->modelo_id ?: null,
        ]);

        return response()->json([
            'mensaje'  => 'Detalle creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle,
        ], 200);
    }

    public function update(Request $r, $promociones_cab_id)
    {
        $r->validate([
            'original_item_id'   => 'required|integer',
            'item_id'            => 'required|integer|exists:items,id',
            'tipo_impuesto_id'   => 'required|integer|exists:tipo_impuesto,id',
            'prom_det_cantidad'  => 'required|numeric|min:0.01',
            'prom_det_costo'     => 'required|numeric|min:0',
            'marca_id'           => 'nullable|integer|exists:marca,id',
            'modelo_id'          => 'nullable|integer|exists:modelo,id',
        ], [
            'item_id.required'           => 'Debe seleccionar un producto.',
            'prom_det_cantidad.required' => 'La cantidad es obligatoria.',
            'prom_det_cantidad.min'      => 'La cantidad debe ser mayor a cero.',
            'prom_det_costo.required'    => 'El costo es obligatorio.',
            'prom_det_costo.min'         => 'El costo no puede ser negativo.',
        ]);

        DB::table('promociones_det')
            ->where('promociones_cab_id', $r->promociones_cab_id)
            ->where('item_id', $r->original_item_id)
            ->update([
                'item_id'           => $r->item_id,
                'tipo_impuesto_id'  => $r->tipo_impuesto_id,
                'prom_det_cantidad' => $r->prom_det_cantidad,
                'prom_det_costo'    => $r->prom_det_costo,
                'marca_id'          => $r->marca_id  ?: null,
                'modelo_id'         => $r->modelo_id ?: null,
            ]);

        return response()->json([
            'mensaje' => 'Detalle modificado con éxito',
            'tipo'    => 'success',
        ], 200);
    }

    public function destroy($promociones_cab_id, $item_id)
    {
        DB::table('promociones_det')
            ->where('promociones_cab_id', $promociones_cab_id)
            ->where('item_id', $item_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Detalle eliminado con éxito',
            'tipo'    => 'success',
        ], 200);
    }
}
