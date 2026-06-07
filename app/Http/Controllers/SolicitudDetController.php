<?php

namespace App\Http\Controllers;
use App\Models\SolicitudDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SolicitudDetController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT
                sd.solicitudes_cab_id,
                sd.item_id,
                sd.soli_det_cantidad,
                sd.soli_det_costo,
                sd.soli_det_cantidad_stock,
                sd.tipo_impuesto_id,
                sd.marca_id,
                sd.modelo_id,
                i.item_decripcion,
                ti.tip_imp_nom,
                ma.marc_nom,
                mo.modelo_nom
            FROM solicitudes_det sd
            JOIN items        i  ON i.id  = sd.item_id
            JOIN tipo_impuesto ti ON ti.id = sd.tipo_impuesto_id
            LEFT JOIN marca   ma ON ma.id = sd.marca_id
            LEFT JOIN modelo  mo ON mo.id = sd.modelo_id
            WHERE sd.solicitudes_cab_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'solicitudes_cab_id'      => 'required|integer|exists:solicitudes_cab,id',
            'item_id'                 => 'required|integer|exists:items,id',
            'tipo_impuesto_id'        => 'required|integer|exists:tipo_impuesto,id',
            'soli_det_cantidad'       => 'required|numeric|min:0.01',
            'soli_det_costo'          => 'required|numeric|min:0',
            'soli_det_cantidad_stock' => 'required|numeric|min:0',
            'marca_id'                => 'nullable|integer|exists:marca,id',
            'modelo_id'               => 'nullable|integer|exists:modelo,id',
        ], [
            'solicitudes_cab_id.required' => 'La solicitud es obligatoria.',
            'item_id.required'            => 'Debe seleccionar un ítem.',
            'item_id.exists'              => 'El ítem seleccionado no es válido.',
            'tipo_impuesto_id.required'   => 'El tipo de impuesto es obligatorio.',
            'soli_det_cantidad.required'  => 'La cantidad es obligatoria.',
            'soli_det_cantidad.min'       => 'La cantidad debe ser mayor a cero.',
            'soli_det_costo.required'     => 'El costo es obligatorio.',
            'soli_det_costo.min'          => 'El costo no puede ser negativo.',
            'marca_id.exists'             => 'La marca seleccionada no es válida.',
            'modelo_id.exists'            => 'El modelo seleccionado no es válido.',
        ]);

        $detalle = new SolicitudDet();
        $detalle->solicitudes_cab_id      = $r->solicitudes_cab_id;
        $detalle->item_id                 = $r->item_id;
        $detalle->tipo_impuesto_id        = $r->tipo_impuesto_id;
        $detalle->soli_det_cantidad       = $r->soli_det_cantidad;
        $detalle->soli_det_costo          = $r->soli_det_costo;
        $detalle->soli_det_cantidad_stock = $r->soli_det_cantidad_stock;
        $detalle->marca_id                = $r->marca_id  ?: null;
        $detalle->modelo_id               = $r->modelo_id ?: null;
        $detalle->save();

        return response()->json([
            'mensaje'  => 'Detalle creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle,
        ]);
    }

    public function update(Request $r, $solicitudes_cab_id)
    {
        $r->validate([
            'item_id'                 => 'required|integer|exists:items,id',
            'tipo_impuesto_id'        => 'required|integer|exists:tipo_impuesto,id',
            'soli_det_cantidad'       => 'required|numeric|min:0.01',
            'soli_det_costo'          => 'required|numeric|min:0',
            'soli_det_cantidad_stock' => 'required|numeric|min:0',
            'marca_id'                => 'nullable|integer|exists:marca,id',
            'modelo_id'               => 'nullable|integer|exists:modelo,id',
        ]);

        DB::table('solicitudes_det')
            ->where('solicitudes_cab_id', $r->solicitudes_cab_id)
            ->update([
                'item_id'                 => $r->item_id,
                'tipo_impuesto_id'        => $r->tipo_impuesto_id,
                'soli_det_cantidad'       => $r->soli_det_cantidad,
                'soli_det_costo'          => $r->soli_det_costo,
                'soli_det_cantidad_stock' => $r->soli_det_cantidad_stock,
                'marca_id'                => $r->marca_id  ?: null,
                'modelo_id'               => $r->modelo_id ?: null,
            ]);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo'    => 'success',
        ], 200);
    }

    public function destroy($solicitudes_cab_id, $item_id)
    {
        DB::table('solicitudes_det')
            ->where('solicitudes_cab_id', $solicitudes_cab_id)
            ->where('item_id', $item_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success',
        ], 200);
    }
}
