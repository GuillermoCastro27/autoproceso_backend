<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotasVentDet;
use Illuminate\Support\Facades\DB;

class NotasVentDetController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT
                nvd.notas_vent_cab_id,
                nvd.item_id,
                nvd.notas_vent_det_cantidad,
                nvd.notas_vent_det_precio,
                nvd.tipo_impuesto_id,
                nvd.deposito_id,
                nvd.marca_id,
                nvd.modelo_id,
                i.item_decripcion,
                i.item_precio,
                ti.tip_imp_nom,
                COALESCE(m.marc_nom, '')  AS marc_nom,
                COALESCE(mo.modelo_nom,'') AS modelo_nom,
                COALESCE(d.dep_nombre,'') AS dep_nombre,
                COALESCE(s.cantidad, 0)   AS stock_disponible
            FROM notas_vent_det nvd
            JOIN items i         ON i.id  = nvd.item_id
            JOIN tipo_impuesto ti ON ti.id = nvd.tipo_impuesto_id
            LEFT JOIN marca m    ON m.id  = nvd.marca_id
            LEFT JOIN modelo mo  ON mo.id = nvd.modelo_id
            LEFT JOIN deposito d ON d.id  = nvd.deposito_id
            LEFT JOIN stock s    ON s.item_id = nvd.item_id AND s.deposito_id = nvd.deposito_id
            WHERE nvd.notas_vent_cab_id = ?
            ORDER BY nvd.item_id
        ", [$id]);
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'notas_vent_cab_id'       => 'required|exists:notas_vent_cab,id',
            'item_id'                 => 'required|exists:items,id',
            'tipo_impuesto_id'        => 'required|exists:tipo_impuesto,id',
            'notas_vent_det_cantidad' => 'required|numeric|min:0.01',
            'notas_vent_det_precio'   => 'required|numeric|min:0',
            'deposito_id'             => 'nullable|integer',
            'marca_id'                => 'nullable|integer',
            'modelo_id'               => 'nullable|integer',
        ]);

        $detalle = NotasVentDet::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Detalle de nota de venta creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 201);
    }

    public function update(Request $request, $notas_vent_cab_id, $item_id)
    {
        $datosValidados = $request->validate([
            'notas_vent_det_cantidad' => 'required|numeric|min:0.01',
            'tipo_impuesto_id'        => 'required|exists:tipo_impuesto,id',
            'notas_vent_det_precio'   => 'required|numeric|min:0',
            'deposito_id'             => 'nullable|integer',
            'marca_id'                => 'nullable|integer',
            'modelo_id'               => 'nullable|integer',
        ]);

        $actualizado = DB::table('notas_vent_det')
            ->where('notas_vent_cab_id', $notas_vent_cab_id)
            ->where('item_id', $item_id)
            ->update(array_merge($datosValidados, ['updated_at' => now()]));

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
