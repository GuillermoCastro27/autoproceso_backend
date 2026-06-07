<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotaCompDet;
use Illuminate\Support\Facades\DB;

class NotasComdetController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT ncd.*,
                   i.item_decripcion, i.item_costo, ti.tip_imp_nom,
                   COALESCE(dep.dep_nombre,'-') AS dep_nombre,
                   COALESCE(ma.marc_nom,'')     AS marc_nom,
                   COALESCE(mo.modelo_nom,'')   AS modelo_nom,
                   COALESCE(s.cantidad, 0)       AS stock_disponible
            FROM notas_comp_det ncd
            JOIN items i          ON i.id  = ncd.item_id
            JOIN tipo_impuesto ti  ON ti.id = ncd.tipo_impuesto_id
            LEFT JOIN deposito dep ON dep.id = ncd.deposito_id
            LEFT JOIN marca ma     ON ma.id  = ncd.marca_id
            LEFT JOIN modelo mo    ON mo.id  = ncd.modelo_id
            LEFT JOIN stock s      ON s.item_id = ncd.item_id AND s.deposito_id = ncd.deposito_id
            WHERE ncd.notas_comp_cab_id = ?
        ", [$id]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'notas_comp_cab_id'       => 'required|exists:notas_comp_cab,id',
            'item_id'                 => 'required|exists:items,id',
            'tipo_impuesto_id'        => 'required|exists:tipo_impuesto,id',
            'notas_comp_det_cantidad' => 'required|numeric|min:0.01',
            'notas_comp_det_costo'    => 'required|numeric|min:0',
            'deposito_id'             => 'nullable|exists:deposito,id',
        ]);

        // Verificar duplicado
        $existe = DB::table('notas_comp_det')
            ->where('notas_comp_cab_id', $request->notas_comp_cab_id)
            ->where('item_id', $request->item_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'Este ítem ya está en el detalle. Modificá el registro existente.',
                'tipo'    => 'warning'
            ], 422);
        }

        $detalle = NotaCompDet::create([
            'notas_comp_cab_id'       => $request->notas_comp_cab_id,
            'item_id'                 => $request->item_id,
            'tipo_impuesto_id'        => $request->tipo_impuesto_id,
            'notas_comp_det_cantidad' => $request->notas_comp_det_cantidad,
            'notas_comp_det_costo'    => $request->notas_comp_det_costo,
            'deposito_id'             => $request->deposito_id ?: null,
            'marca_id'                => $request->marca_id  ?: null,
            'modelo_id'               => $request->modelo_id ?: null,
        ]);

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 201);
    }

    public function update(Request $request, $notas_comp_cab_id, $item_id)
    {
        $request->validate([
            'notas_comp_det_cantidad' => 'required|numeric|min:0.01',
            'tipo_impuesto_id'        => 'required|exists:tipo_impuesto,id',
            'notas_comp_det_costo'    => 'required|numeric|min:0',
            'deposito_id'             => 'nullable|exists:deposito,id',
        ]);

        $actualizado = DB::table('notas_comp_det')
            ->where('notas_comp_cab_id', $notas_comp_cab_id)
            ->where('item_id', $item_id)
            ->update([
                'notas_comp_det_cantidad' => $request->notas_comp_det_cantidad,
                'notas_comp_det_costo'    => $request->notas_comp_det_costo,
                'tipo_impuesto_id'        => $request->tipo_impuesto_id,
                'deposito_id'             => $request->deposito_id ?: null,
                'marca_id'               => $request->marca_id  ?: null,
                'modelo_id'              => $request->modelo_id ?: null,
                'updated_at'             => now(),
            ]);

        if ($actualizado === 0) {
            return response()->json(['mensaje' => 'Detalle no encontrado', 'tipo' => 'error'], 404);
        }

        return response()->json(['mensaje' => 'Registro modificado con éxito', 'tipo' => 'success'], 200);
    }

    public function destroy($notas_comp_cab_id, $item_id)
    {
        NotaCompDet::where('notas_comp_cab_id', $notas_comp_cab_id)
            ->where('item_id', $item_id)
            ->delete();

        return response()->json(['mensaje' => 'Registro eliminado con éxito', 'tipo' => 'success'], 200);
    }
}
