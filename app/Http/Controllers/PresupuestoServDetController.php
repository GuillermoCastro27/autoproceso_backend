<?php

namespace App\Http\Controllers;

use App\Models\PresupuestoServDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PresupuestoServDetController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT
                psd.presupuesto_serv_cab_id,
                psd.item_id,
                psd.pres_serv_det_cantidad,
                psd.pres_serv_det_costo,
                psd.pres_serv_det_cantidad_stock,
                psd.tipo_impuesto_id,
                psd.marca_id,
                psd.modelo_id,
                i.item_decripcion,
                ti.tip_imp_nom,
                ma.marc_nom,
                mo.modelo_nom,
                tp.tipo_prom_modo,
                tp.tipo_prom_valor,
                dc.desc_cab_porcentaje
            FROM presupuesto_serv_det psd
            JOIN items         i  ON i.id  = psd.item_id
            JOIN tipo_impuesto ti ON ti.id = psd.tipo_impuesto_id
            LEFT JOIN marca    ma ON ma.id = psd.marca_id
            LEFT JOIN modelo   mo ON mo.id = psd.modelo_id
            LEFT JOIN presupuesto_serv_cab psc ON psc.id = psd.presupuesto_serv_cab_id
            LEFT JOIN promociones_cab pc  ON pc.id  = psc.promociones_cab_id
            LEFT JOIN tipo_promociones tp ON tp.id  = pc.tipo_promociones_id
            LEFT JOIN descuentos_cab dc   ON dc.id  = psc.descuentos_cab_id
            WHERE psd.presupuesto_serv_cab_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'presupuesto_serv_cab_id'       => 'required|integer|exists:presupuesto_serv_cab,id',
            'item_id'                       => 'required|integer|exists:items,id',
            'tipo_impuesto_id'              => 'required|integer|exists:tipo_impuesto,id',
            'pres_serv_det_cantidad'        => 'required|numeric|min:0.01',
            'pres_serv_det_costo'           => 'required|numeric|min:0',
            'pres_serv_det_cantidad_stock'  => 'required|numeric|min:0',
            'marca_id'                      => 'nullable|integer|exists:marca,id',
            'modelo_id'                     => 'nullable|integer|exists:modelo,id',
        ], [
            'presupuesto_serv_cab_id.required' => 'El presupuesto es obligatorio.',
            'item_id.required'                 => 'Debe seleccionar un ítem.',
            'item_id.exists'                   => 'El ítem seleccionado no es válido.',
            'tipo_impuesto_id.required'        => 'El tipo de impuesto es obligatorio.',
            'pres_serv_det_cantidad.required'  => 'La cantidad es obligatoria.',
            'pres_serv_det_cantidad.min'       => 'La cantidad debe ser mayor a cero.',
            'pres_serv_det_costo.required'     => 'El costo es obligatorio.',
            'pres_serv_det_costo.min'          => 'El costo no puede ser negativo.',
        ]);

        $detalle = new PresupuestoServDet();
        $detalle->presupuesto_serv_cab_id      = $r->presupuesto_serv_cab_id;
        $detalle->item_id                      = $r->item_id;
        $detalle->tipo_impuesto_id             = $r->tipo_impuesto_id;
        $detalle->pres_serv_det_cantidad       = $r->pres_serv_det_cantidad;
        $detalle->pres_serv_det_costo          = $r->pres_serv_det_costo;
        $detalle->pres_serv_det_cantidad_stock = $r->pres_serv_det_cantidad_stock;
        $detalle->marca_id                     = $r->marca_id  ?: null;
        $detalle->modelo_id                    = $r->modelo_id ?: null;
        $detalle->save();

        return response()->json([
            'mensaje'  => 'Detalle creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle,
        ]);
    }

    public function update(Request $r, $presupuesto_serv_cab_id)
    {
        $r->validate([
            'item_id'                      => 'required|integer|exists:items,id',
            'tipo_impuesto_id'             => 'required|integer|exists:tipo_impuesto,id',
            'pres_serv_det_cantidad'       => 'required|numeric|min:0.01',
            'pres_serv_det_costo'          => 'required|numeric|min:0',
            'pres_serv_det_cantidad_stock' => 'required|numeric|min:0',
            'marca_id'                     => 'nullable|integer|exists:marca,id',
            'modelo_id'                    => 'nullable|integer|exists:modelo,id',
        ]);

        DB::table('presupuesto_serv_det')
            ->where('presupuesto_serv_cab_id', $r->presupuesto_serv_cab_id)
            ->update([
                'item_id'                      => $r->item_id,
                'tipo_impuesto_id'             => $r->tipo_impuesto_id,
                'pres_serv_det_cantidad'       => $r->pres_serv_det_cantidad,
                'pres_serv_det_costo'          => $r->pres_serv_det_costo,
                'pres_serv_det_cantidad_stock' => $r->pres_serv_det_cantidad_stock,
                'marca_id'                     => $r->marca_id  ?: null,
                'modelo_id'                    => $r->modelo_id ?: null,
            ]);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo'    => 'success',
        ], 200);
    }

    public function destroy($presupuesto_serv_cab_id, $item_id)
    {
        DB::table('presupuesto_serv_det')
            ->where('presupuesto_serv_cab_id', $presupuesto_serv_cab_id)
            ->where('item_id', $item_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success',
        ], 200);
    }
}
