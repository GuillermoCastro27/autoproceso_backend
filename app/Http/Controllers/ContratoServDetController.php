<?php

namespace App\Http\Controllers;

use App\Models\ContratoServDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ContratoServDetController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT
                csd.contrato_serv_cab_id,
                csd.item_id,
                csd.contrato_serv_det_cantidad,
                csd.contrato_serv_det_costo,
                csd.contrato_serv_det_cantidad_stock,
                csd.tipo_impuesto_id,
                i.item_decripcion,
                ti.tip_imp_nom
            FROM contrato_serv_det csd
            JOIN items         i  ON i.id  = csd.item_id
            JOIN tipo_impuesto ti ON ti.id = csd.tipo_impuesto_id
            WHERE csd.contrato_serv_cab_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'contrato_serv_cab_id'              => 'required|integer|exists:contrato_serv_cab,id',
            'item_id'                           => 'required|integer|exists:items,id',
            'tipo_impuesto_id'                  => 'required|integer|exists:tipo_impuesto,id',
            'contrato_serv_det_cantidad'        => 'required|numeric|min:0.01',
            'contrato_serv_det_costo'           => 'required|numeric|min:0',
            'contrato_serv_det_cantidad_stock'  => 'required|numeric|min:0',
        ], [
            'contrato_serv_cab_id.required'    => 'El contrato es obligatorio.',
            'item_id.required'                 => 'Debe seleccionar un ítem.',
            'item_id.exists'                   => 'El ítem seleccionado no es válido.',
            'tipo_impuesto_id.required'        => 'El tipo de impuesto es obligatorio.',
            'contrato_serv_det_cantidad.required' => 'La cantidad es obligatoria.',
            'contrato_serv_det_cantidad.min'   => 'La cantidad debe ser mayor a cero.',
            'contrato_serv_det_costo.required' => 'El costo es obligatorio.',
            'contrato_serv_det_costo.min'      => 'El costo no puede ser negativo.',
        ]);

        $detalle = new ContratoServDet();
        $detalle->contrato_serv_cab_id             = $r->contrato_serv_cab_id;
        $detalle->item_id                          = $r->item_id;
        $detalle->tipo_impuesto_id                 = $r->tipo_impuesto_id;
        $detalle->contrato_serv_det_cantidad       = $r->contrato_serv_det_cantidad;
        $detalle->contrato_serv_det_costo          = $r->contrato_serv_det_costo;
        $detalle->contrato_serv_det_cantidad_stock = $r->contrato_serv_det_cantidad_stock;
        $detalle->save();

        return response()->json([
            'mensaje'  => 'Detalle creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle,
        ]);
    }

    public function update(Request $r, $contrato_serv_cab_id)
    {
        $r->validate([
            'item_id'                          => 'required|integer|exists:items,id',
            'tipo_impuesto_id'                 => 'required|integer|exists:tipo_impuesto,id',
            'contrato_serv_det_cantidad'       => 'required|numeric|min:0.01',
            'contrato_serv_det_costo'          => 'required|numeric|min:0',
            'contrato_serv_det_cantidad_stock' => 'required|numeric|min:0',
        ]);

        DB::table('contrato_serv_det')
            ->where('contrato_serv_cab_id', $r->contrato_serv_cab_id)
            ->where('item_id', $r->original_item_id ?: $r->item_id)
            ->update([
                'item_id'                          => $r->item_id,
                'tipo_impuesto_id'                 => $r->tipo_impuesto_id,
                'contrato_serv_det_cantidad'       => $r->contrato_serv_det_cantidad,
                'contrato_serv_det_costo'          => $r->contrato_serv_det_costo,
                'contrato_serv_det_cantidad_stock' => $r->contrato_serv_det_cantidad_stock,
            ]);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo'    => 'success',
        ], 200);
    }

    public function destroy($contrato_serv_cab_id, $item_id)
    {
        DB::table('contrato_serv_det')
            ->where('contrato_serv_cab_id', $contrato_serv_cab_id)
            ->where('item_id', $item_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success',
        ], 200);
    }
}
