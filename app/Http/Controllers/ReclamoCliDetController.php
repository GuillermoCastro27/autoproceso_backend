<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReclamoCliDet;
use Illuminate\Support\Facades\DB;

class ReclamoCliDetController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT
                rcd.reclamo_cli_cab_id,
                rcd.item_id,
                rcd.rec_cli_det_cantidad,
                rcd.rec_cli_det_costo,
                rcd.rec_cli_det_cantidad_stock,
                rcd.tipo_impuesto_id,
                rcd.marca_id,
                rcd.modelo_id,
                i.item_decripcion,
                ti.tip_imp_nom,
                COALESCE(m.marc_nom,   '') AS marc_nom,
                COALESCE(mo.modelo_nom,'') AS modelo_nom
            FROM reclamo_cli_det rcd
            JOIN items i          ON i.id  = rcd.item_id
            JOIN tipo_impuesto ti  ON ti.id = rcd.tipo_impuesto_id
            LEFT JOIN marca m      ON m.id  = rcd.marca_id
            LEFT JOIN modelo mo    ON mo.id = rcd.modelo_id
            WHERE rcd.reclamo_cli_cab_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'reclamo_cli_cab_id'       => 'required|integer|exists:reclamo_cli_cab,id',
            'item_id'                  => 'required|integer|exists:items,id',
            'tipo_impuesto_id'         => 'required|integer|exists:tipo_impuesto,id',
            'rec_cli_det_cantidad'     => 'required|numeric|min:0.01',
            'rec_cli_det_costo'        => 'required|numeric|min:0',
            'rec_cli_det_cantidad_stock' => 'required|numeric|min:0',
            'marca_id'                 => 'nullable|integer|exists:marca,id',
            'modelo_id'                => 'nullable|integer|exists:modelo,id',
        ], [
            'reclamo_cli_cab_id.required' => 'El reclamo es obligatorio.',
            'item_id.required'            => 'Debe seleccionar un producto.',
            'item_id.exists'              => 'El producto seleccionado no es válido.',
            'tipo_impuesto_id.required'   => 'El tipo de impuesto es obligatorio.',
            'rec_cli_det_cantidad.required' => 'La cantidad es obligatoria.',
            'rec_cli_det_cantidad.min'      => 'La cantidad debe ser mayor a cero.',
            'rec_cli_det_costo.required'    => 'El precio es obligatorio.',
            'rec_cli_det_costo.min'         => 'El precio no puede ser negativo.',
            'rec_cli_det_cantidad_stock.required' => 'La cantidad disponible es obligatoria.',
            'rec_cli_det_cantidad_stock.min'      => 'La cantidad disponible no puede ser negativa.',
        ]);

        $detalle = ReclamoCliDet::create([
            'reclamo_cli_cab_id'         => $r->reclamo_cli_cab_id,
            'item_id'                    => $r->item_id,
            'tipo_impuesto_id'           => $r->tipo_impuesto_id,
            'rec_cli_det_cantidad'       => $r->rec_cli_det_cantidad,
            'rec_cli_det_costo'          => $r->rec_cli_det_costo,
            'rec_cli_det_cantidad_stock' => $r->rec_cli_det_cantidad_stock,
            'marca_id'                   => $r->marca_id  ?: null,
            'modelo_id'                  => $r->modelo_id ?: null,
        ]);

        return response()->json([
            'mensaje'  => 'Detalle creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle,
        ], 200);
    }

    public function update(Request $r, $reclamo_cli_cab_id)
    {
        $r->validate([
            'original_item_id'           => 'required|integer',
            'item_id'                    => 'required|integer|exists:items,id',
            'tipo_impuesto_id'           => 'required|integer|exists:tipo_impuesto,id',
            'rec_cli_det_cantidad'       => 'required|numeric|min:0.01',
            'rec_cli_det_costo'          => 'required|numeric|min:0',
            'rec_cli_det_cantidad_stock' => 'required|numeric|min:0',
            'marca_id'                   => 'nullable|integer|exists:marca,id',
            'modelo_id'                  => 'nullable|integer|exists:modelo,id',
        ], [
            'item_id.required'           => 'Debe seleccionar un producto.',
            'rec_cli_det_cantidad.required' => 'La cantidad es obligatoria.',
            'rec_cli_det_cantidad.min'      => 'La cantidad debe ser mayor a cero.',
            'rec_cli_det_costo.required'    => 'El precio es obligatorio.',
            'rec_cli_det_costo.min'         => 'El precio no puede ser negativo.',
        ]);

        DB::table('reclamo_cli_det')
            ->where('reclamo_cli_cab_id', $reclamo_cli_cab_id)
            ->where('item_id', $r->original_item_id)
            ->update([
                'item_id'                    => $r->item_id,
                'tipo_impuesto_id'           => $r->tipo_impuesto_id,
                'rec_cli_det_cantidad'       => $r->rec_cli_det_cantidad,
                'rec_cli_det_costo'          => $r->rec_cli_det_costo,
                'rec_cli_det_cantidad_stock' => $r->rec_cli_det_cantidad_stock,
                'marca_id'                   => $r->marca_id  ?: null,
                'modelo_id'                  => $r->modelo_id ?: null,
            ]);

        return response()->json([
            'mensaje' => 'Detalle modificado con éxito',
            'tipo'    => 'success',
        ], 200);
    }

    public function destroy($reclamo_cli_cab_id, $item_id)
    {
        DB::table('reclamo_cli_det')
            ->where('reclamo_cli_cab_id', $reclamo_cli_cab_id)
            ->where('item_id', $item_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Detalle eliminado con éxito',
            'tipo'    => 'success',
        ], 200);
    }
}
