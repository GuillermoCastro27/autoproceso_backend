<?php

namespace App\Http\Controllers;

use App\Models\InsumosUtilizados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsumosUtilizadosController extends Controller
{
    public function readAll()
    {
        return DB::select("
            SELECT
                iu.id,
                iu.orden_serv_cab_id,
                iu.item_id,
                iu.tipo_impuesto_id,
                i.item_decripcion,
                t.tipo_descripcion,
                ti.tip_imp_nom,
                c.cli_nombre,
                c.cli_apellido,
                iu.ins_util_cantidad,
                iu.ins_util_costo,
                iu.ins_util_estado,
                ROUND((iu.ins_util_cantidad * iu.ins_util_costo)::numeric, 2) AS subtotal
            FROM insumos_utilizados iu
            JOIN items i            ON i.id  = iu.item_id
            JOIN tipos t            ON t.id  = i.tipo_id
            JOIN tipo_impuesto ti   ON ti.id = iu.tipo_impuesto_id
            JOIN orden_serv_cab osc ON osc.id = iu.orden_serv_cab_id
            JOIN clientes c         ON c.id  = osc.clientes_id
            ORDER BY iu.id DESC
        ");
    }

    public function readByOrden($orden_serv_cab_id)
    {
        return DB::select("
            SELECT
                iu.id,
                iu.orden_serv_cab_id,
                iu.item_id,
                iu.tipo_impuesto_id,
                i.item_decripcion,
                t.tipo_descripcion,
                ti.tip_imp_nom,
                iu.ins_util_cantidad,
                iu.ins_util_costo,
                iu.ins_util_estado,
                ROUND((iu.ins_util_cantidad * iu.ins_util_costo)::numeric, 2) AS total,
                ROUND(
                    CASE
                        WHEN ti.tip_imp_nom = 'IVA10' THEN (iu.ins_util_cantidad * iu.ins_util_costo) / 11
                        WHEN ti.tip_imp_nom = 'IVA5'  THEN (iu.ins_util_cantidad * iu.ins_util_costo) / 21
                        ELSE 0
                    END::numeric, 2
                ) AS iva
            FROM insumos_utilizados iu
            JOIN items i          ON i.id  = iu.item_id
            JOIN tipos t          ON t.id  = i.tipo_id
            JOIN tipo_impuesto ti ON ti.id = iu.tipo_impuesto_id
            WHERE iu.orden_serv_cab_id = ?
            ORDER BY iu.id ASC
        ", [$orden_serv_cab_id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'orden_serv_cab_id' => 'required|integer|exists:orden_serv_cab,id',
            'item_id'           => 'required|integer|exists:items,id',
            'tipo_impuesto_id'  => 'required|integer|exists:tipo_impuesto,id',
            'ins_util_cantidad' => 'required|numeric|min:0.01',
            'ins_util_costo'    => 'required|numeric|min:0',
        ]);

        $insumo = InsumosUtilizados::create([
            'orden_serv_cab_id' => $r->orden_serv_cab_id,
            'item_id'           => $r->item_id,
            'tipo_impuesto_id'  => $r->tipo_impuesto_id,
            'ins_util_cantidad' => $r->ins_util_cantidad,
            'ins_util_costo'    => $r->ins_util_costo,
            'ins_util_estado'   => 'PENDIENTE',
        ]);

        return response()->json([
            'mensaje'  => 'Insumo registrado correctamente.',
            'tipo'     => 'success',
            'registro' => $insumo,
        ], 201);
    }

    public function update(Request $r, $id)
    {
        $insumo = InsumosUtilizados::find($id);
        if (!$insumo) {
            return response()->json(['mensaje' => 'Insumo no encontrado.', 'tipo' => 'error'], 404);
        }
        if ($insumo->ins_util_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se puede modificar un insumo en estado PENDIENTE.', 'tipo' => 'error'], 422);
        }

        $r->validate([
            'ins_util_cantidad' => 'required|numeric|min:0.01',
            'ins_util_costo'    => 'required|numeric|min:0',
        ]);

        $insumo->update([
            'ins_util_cantidad' => $r->ins_util_cantidad,
            'ins_util_costo'    => $r->ins_util_costo,
        ]);

        return response()->json([
            'mensaje'  => 'Insumo actualizado correctamente.',
            'tipo'     => 'success',
            'registro' => $insumo,
        ]);
    }

    public function confirmar($id)
    {
        $insumo = InsumosUtilizados::find($id);
        if (!$insumo) {
            return response()->json(['mensaje' => 'Insumo no encontrado.', 'tipo' => 'error'], 404);
        }
        if ($insumo->ins_util_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden confirmar insumos en estado PENDIENTE. Estado actual: ' . $insumo->ins_util_estado,
                'tipo'    => 'error',
            ], 422);
        }

        $insumo->update(['ins_util_estado' => 'CONFIRMADO']);

        return response()->json(['mensaje' => 'Insumo confirmado correctamente.', 'tipo' => 'success']);
    }

    public function anular($id)
    {
        $insumo = InsumosUtilizados::find($id);
        if (!$insumo) {
            return response()->json(['mensaje' => 'Insumo no encontrado.', 'tipo' => 'error'], 404);
        }
        if ($insumo->ins_util_estado === 'ANULADO') {
            return response()->json(['mensaje' => 'El insumo ya está anulado.', 'tipo' => 'error'], 422);
        }

        $insumo->update(['ins_util_estado' => 'ANULADO']);

        return response()->json(['mensaje' => 'Insumo anulado correctamente.', 'tipo' => 'success']);
    }

    public function destroy($id)
    {
        $insumo = InsumosUtilizados::find($id);
        if (!$insumo) {
            return response()->json(['mensaje' => 'Insumo no encontrado.', 'tipo' => 'error'], 404);
        }

        $insumo->delete();

        return response()->json(['mensaje' => 'Insumo eliminado correctamente.', 'tipo' => 'success']);
    }
}
