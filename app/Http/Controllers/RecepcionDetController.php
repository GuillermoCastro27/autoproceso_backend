<?php

namespace App\Http\Controllers;
use App\Models\RecepcionDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RecepcionDetController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT
                rd.recep_cab_id,
                rd.item_id,
                rd.recep_det_cantidad,
                rd.recep_det_costo,
                rd.recep_det_cantidad_stock,
                rd.tipo_impuesto_id,
                rd.marca_id,
                rd.modelo_id,
                i.item_decripcion,
                ti.tip_imp_nom,
                ma.marc_nom,
                mo.modelo_nom
            FROM recep_det rd
            JOIN items         i  ON i.id  = rd.item_id
            JOIN tipo_impuesto ti ON ti.id = rd.tipo_impuesto_id
            LEFT JOIN marca    ma ON ma.id = rd.marca_id
            LEFT JOIN modelo   mo ON mo.id = rd.modelo_id
            WHERE rd.recep_cab_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'recep_cab_id'              => 'required|integer|exists:recep_cab,id',
            'item_id'                   => 'required|integer|exists:items,id',
            'tipo_impuesto_id'          => 'required|integer|exists:tipo_impuesto,id',
            'recep_det_cantidad'        => 'required|numeric|min:0.01',
            'recep_det_costo'           => 'required|numeric|min:0',
            'recep_det_cantidad_stock'  => 'required|numeric|min:0',
            'marca_id'                  => 'nullable|integer|exists:marca,id',
            'modelo_id'                 => 'nullable|integer|exists:modelo,id',
        ], [
            'recep_cab_id.required'         => 'La recepción es obligatoria.',
            'item_id.required'              => 'Debe seleccionar un ítem.',
            'item_id.exists'                => 'El ítem seleccionado no es válido.',
            'tipo_impuesto_id.required'     => 'El tipo de impuesto es obligatorio.',
            'recep_det_cantidad.required'   => 'La cantidad es obligatoria.',
            'recep_det_cantidad.min'        => 'La cantidad debe ser mayor a cero.',
            'recep_det_costo.required'      => 'El costo es obligatorio.',
            'recep_det_costo.min'           => 'El costo no puede ser negativo.',
            'marca_id.exists'               => 'La marca seleccionada no es válida.',
            'modelo_id.exists'              => 'El modelo seleccionado no es válido.',
        ]);

        $detalle = new RecepcionDet();
        $detalle->recep_cab_id             = $r->recep_cab_id;
        $detalle->item_id                  = $r->item_id;
        $detalle->tipo_impuesto_id         = $r->tipo_impuesto_id;
        $detalle->recep_det_cantidad       = $r->recep_det_cantidad;
        $detalle->recep_det_costo          = $r->recep_det_costo;
        $detalle->recep_det_cantidad_stock = $r->recep_det_cantidad_stock;
        $detalle->marca_id                 = $r->marca_id  ?: null;
        $detalle->modelo_id                = $r->modelo_id ?: null;
        $detalle->save();

        return response()->json([
            'mensaje'  => 'Detalle creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle,
        ]);
    }

    public function update(Request $r, $recep_cab_id)
    {
        $r->validate([
            'item_id'                  => 'required|integer|exists:items,id',
            'tipo_impuesto_id'         => 'required|integer|exists:tipo_impuesto,id',
            'recep_det_cantidad'       => 'required|numeric|min:0.01',
            'recep_det_costo'          => 'required|numeric|min:0',
            'recep_det_cantidad_stock' => 'required|numeric|min:0',
            'marca_id'                 => 'nullable|integer|exists:marca,id',
            'modelo_id'                => 'nullable|integer|exists:modelo,id',
        ]);

        DB::table('recep_det')
            ->where('recep_cab_id', $r->recep_cab_id)
            ->update([
                'item_id'                  => $r->item_id,
                'tipo_impuesto_id'         => $r->tipo_impuesto_id,
                'recep_det_cantidad'       => $r->recep_det_cantidad,
                'recep_det_costo'          => $r->recep_det_costo,
                'recep_det_cantidad_stock' => $r->recep_det_cantidad_stock,
                'marca_id'                 => $r->marca_id  ?: null,
                'modelo_id'                => $r->modelo_id ?: null,
            ]);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo'    => 'success',
        ], 200);
    }

    public function destroy($recep_cab_id, $item_id)
    {
        DB::table('recep_det')
            ->where('recep_cab_id', $recep_cab_id)
            ->where('item_id', $item_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success',
        ], 200);
    }
}
