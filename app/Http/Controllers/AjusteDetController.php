<?php

namespace App\Http\Controllers;

use App\Models\AjusteDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AjusteDetController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT ad.ajuste_cab_id, ad.item_id, ad.deposito_id,
                   ad.ajus_det_cantidad, ad.cantidad_stock,
                   ad.marca_id, ad.modelo_id,
                   i.item_decripcion,
                   COALESCE(dep.dep_nombre,'-') AS dep_nombre,
                   COALESCE(ma.marc_nom,'')     AS marc_nom,
                   COALESCE(mo.modelo_nom,'')   AS modelo_nom
            FROM ajuste_det ad
            JOIN items i ON i.id = ad.item_id
            LEFT JOIN deposito dep ON dep.id = ad.deposito_id
            LEFT JOIN marca ma     ON ma.id  = ad.marca_id
            LEFT JOIN modelo mo    ON mo.id  = ad.modelo_id
            WHERE ad.ajuste_cab_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'ajuste_cab_id'      => 'required|exists:ajuste_cab,id',
            'item_id'            => 'required|exists:items,id',
            'ajus_det_cantidad'  => 'required|numeric|min:0.01',
            'cantidad_stock'     => 'nullable|numeric',
            'deposito_id'        => 'nullable|exists:deposito,id',
            'marca_id'           => 'nullable|exists:marca,id',
            'modelo_id'          => 'nullable|exists:modelo,id',
        ]);

        // Verificar duplicado
        $existe = DB::table('ajuste_det')
            ->where('ajuste_cab_id', $r->ajuste_cab_id)
            ->where('item_id', $r->item_id)
            ->where('deposito_id', $r->deposito_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'Este ítem ya está en el detalle con el mismo depósito. Modificá el registro existente.',
                'tipo'    => 'warning'
            ], 422);
        }

        $detalle = AjusteDet::create([
            'ajuste_cab_id'     => $r->ajuste_cab_id,
            'item_id'           => $r->item_id,
            'deposito_id'       => $r->deposito_id ?: null,
            'ajus_det_cantidad' => $r->ajus_det_cantidad,
            'cantidad_stock'    => $r->cantidad_stock ?? 0,
            'marca_id'          => $r->marca_id  ?: null,
            'modelo_id'         => $r->modelo_id ?: null,
        ]);

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 200);
    }

    public function update(Request $r, $ajuste_cab_id)
    {
        $r->validate([
            'item_id'           => 'required|exists:items,id',
            'ajus_det_cantidad' => 'required|numeric|min:0.01',
            'cantidad_stock'    => 'nullable|numeric',
            'deposito_id'       => 'nullable|exists:deposito,id',
        ]);

        DB::table('ajuste_det')
            ->where('ajuste_cab_id', $ajuste_cab_id)
            ->where('item_id', $r->item_id)
            ->update([
                'ajus_det_cantidad' => $r->ajus_det_cantidad,
                'cantidad_stock'    => $r->cantidad_stock ?? 0,
                'deposito_id'       => $r->deposito_id ?: null,
            ]);

        $detalle = DB::select("
            SELECT * FROM ajuste_det WHERE ajuste_cab_id = ? AND item_id = ?
        ", [$ajuste_cab_id, $r->item_id]);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 200);
    }

    public function destroy(Request $r, $ajuste_cab_id, $item_id)
    {
        DB::table('ajuste_det')
            ->where('ajuste_cab_id', $ajuste_cab_id)
            ->where('item_id', $item_id)
            ->where('deposito_id', $r->deposito_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }
}
