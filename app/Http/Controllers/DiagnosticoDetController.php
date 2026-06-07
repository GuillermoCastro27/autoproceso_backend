<?php

namespace App\Http\Controllers;
use App\Models\DiagnosticoDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DiagnosticoDetController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT
                dd.diagnostico_cab_id,
                dd.item_id,
                dd.diag_det_cantidad,
                dd.diag_det_costo,
                dd.diag_det_cantidad_stock,
                dd.tipo_impuesto_id,
                dd.marca_id,
                dd.modelo_id,
                i.item_decripcion,
                ti.tip_imp_nom,
                ma.marc_nom,
                mo.modelo_nom
            FROM diagnostico_det dd
            JOIN items         i  ON i.id  = dd.item_id
            JOIN tipo_impuesto ti ON ti.id = dd.tipo_impuesto_id
            LEFT JOIN marca    ma ON ma.id = dd.marca_id
            LEFT JOIN modelo   mo ON mo.id = dd.modelo_id
            WHERE dd.diagnostico_cab_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'diagnostico_cab_id'    => 'required|integer|exists:diagnostico_cab,id',
            'item_id'               => 'required|integer|exists:items,id',
            'tipo_impuesto_id'      => 'required|integer|exists:tipo_impuesto,id',
            'diag_det_cantidad'     => 'required|numeric|min:0.01',
            'diag_det_costo'        => 'required|numeric|min:0',
            'diag_det_cantidad_stock'=> 'required|numeric|min:0',
            'marca_id'              => 'nullable|integer|exists:marca,id',
            'modelo_id'             => 'nullable|integer|exists:modelo,id',
        ], [
            'diagnostico_cab_id.required' => 'El diagnóstico es obligatorio.',
            'item_id.required'            => 'Debe seleccionar un ítem.',
            'item_id.exists'              => 'El ítem seleccionado no es válido.',
            'tipo_impuesto_id.required'   => 'El tipo de impuesto es obligatorio.',
            'diag_det_cantidad.required'  => 'La cantidad es obligatoria.',
            'diag_det_cantidad.min'       => 'La cantidad debe ser mayor a cero.',
            'diag_det_costo.required'     => 'El costo es obligatorio.',
            'diag_det_costo.min'          => 'El costo no puede ser negativo.',
            'marca_id.exists'             => 'La marca seleccionada no es válida.',
            'modelo_id.exists'            => 'El modelo seleccionado no es válido.',
        ]);

        $detalle = new DiagnosticoDet();
        $detalle->diagnostico_cab_id      = $r->diagnostico_cab_id;
        $detalle->item_id                 = $r->item_id;
        $detalle->tipo_impuesto_id        = $r->tipo_impuesto_id;
        $detalle->diag_det_cantidad       = $r->diag_det_cantidad;
        $detalle->diag_det_costo          = $r->diag_det_costo;
        $detalle->diag_det_cantidad_stock = $r->diag_det_cantidad_stock;
        $detalle->marca_id                = $r->marca_id  ?: null;
        $detalle->modelo_id               = $r->modelo_id ?: null;
        $detalle->save();

        return response()->json([
            'mensaje'  => 'Detalle creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle,
        ]);
    }

    public function update(Request $r, $diagnostico_cab_id)
    {
        $r->validate([
            'item_id'               => 'required|integer|exists:items,id',
            'tipo_impuesto_id'      => 'required|integer|exists:tipo_impuesto,id',
            'diag_det_cantidad'     => 'required|numeric|min:0.01',
            'diag_det_costo'        => 'required|numeric|min:0',
            'diag_det_cantidad_stock'=> 'required|numeric|min:0',
            'marca_id'              => 'nullable|integer|exists:marca,id',
            'modelo_id'             => 'nullable|integer|exists:modelo,id',
        ]);

        DB::table('diagnostico_det')
            ->where('diagnostico_cab_id', $r->diagnostico_cab_id)
            ->update([
                'item_id'               => $r->item_id,
                'tipo_impuesto_id'      => $r->tipo_impuesto_id,
                'diag_det_cantidad'     => $r->diag_det_cantidad,
                'diag_det_costo'        => $r->diag_det_costo,
                'diag_det_cantidad_stock'=> $r->diag_det_cantidad_stock,
                'marca_id'              => $r->marca_id  ?: null,
                'modelo_id'             => $r->modelo_id ?: null,
            ]);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo'    => 'success',
        ], 200);
    }

    public function destroy($diagnostico_cab_id, $item_id)
    {
        DB::table('diagnostico_det')
            ->where('diagnostico_cab_id', $diagnostico_cab_id)
            ->where('item_id', $item_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success',
        ], 200);
    }
}
