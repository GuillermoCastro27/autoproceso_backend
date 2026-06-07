<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\NotaRemiComDet;

class NotaRemiComDetController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT nrcd.*,
                   i.item_decripcion,
                   COALESCE(dor.dep_nombre, '-') AS dep_origen_nombre,
                   COALESCE(dds.dep_nombre, '-') AS dep_destino_nombre,
                   COALESCE(ma.marc_nom,'')       AS marc_nom,
                   COALESCE(mo.modelo_nom,'')     AS modelo_nom
            FROM nota_remi_com_det nrcd
            JOIN items i ON i.id = nrcd.item_id
            LEFT JOIN deposito dor ON dor.id = nrcd.deposito_id
            LEFT JOIN deposito dds ON dds.id = nrcd.deposito_destino_id
            LEFT JOIN marca ma    ON ma.id  = nrcd.marca_id
            LEFT JOIN modelo mo   ON mo.id  = nrcd.modelo_id
            WHERE nrcd.nota_remi_comp_id = ?
        ", [$id]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'nota_remi_comp_id'          => 'required|exists:nota_remi_comp,id',
            'item_id'                    => 'required|exists:items,id',
            'nota_remi_com_det_cantidad' => 'required|numeric|min:0.01',
            'deposito_id'                => 'required|exists:deposito,id',
            'deposito_destino_id'        => 'nullable|exists:deposito,id',
        ]);

        $nota = DB::table('nota_remi_comp')->where('id', $r->nota_remi_comp_id)->first();

        // Depósito origen debe pertenecer a la sucursal origen
        $depOrigen = DB::table('deposito')->where('id', $r->deposito_id)->first();
        if (!$depOrigen || $depOrigen->sucursal_id != $nota->sucursal_id) {
            return response()->json([
                'mensaje' => 'El depósito origen no pertenece a la sucursal origen de la nota.',
                'tipo'    => 'error'
            ], 422);
        }

        if ($nota->tipo === 'TRANSFERENCIA') {
            // Depósito destino requerido
            if (!$r->deposito_destino_id) {
                return response()->json([
                    'mensaje' => 'El depósito destino es obligatorio para transferencias.',
                    'tipo'    => 'error'
                ], 422);
            }

            // Depósito destino debe pertenecer a la sucursal destino
            $depDestino = DB::table('deposito')->where('id', $r->deposito_destino_id)->first();
            if (!$depDestino || $depDestino->sucursal_id != $nota->sucursal_destino_id) {
                return response()->json([
                    'mensaje' => 'El depósito destino no pertenece a la sucursal destino de la nota.',
                    'tipo'    => 'error'
                ], 422);
            }

            // Depósito origen ≠ depósito destino
            if ($r->deposito_id == $r->deposito_destino_id) {
                return response()->json([
                    'mensaje' => 'El depósito origen y el destino no pueden ser el mismo.',
                    'tipo'    => 'error'
                ], 422);
            }
        }

        // Ítem duplicado: misma nota + mismo ítem + mismo depósito origen
        $existe = DB::table('nota_remi_com_det')
            ->where('nota_remi_comp_id', $r->nota_remi_comp_id)
            ->where('item_id', $r->item_id)
            ->where('deposito_id', $r->deposito_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'Este ítem ya está registrado en el detalle con el mismo depósito origen. Modificá el registro existente.',
                'tipo'    => 'warning'
            ], 422);
        }

        $detalle = NotaRemiComDet::create([
            'nota_remi_comp_id'          => $r->nota_remi_comp_id,
            'item_id'                    => $r->item_id,
            'nota_remi_com_det_cantidad' => $r->nota_remi_com_det_cantidad,
            'deposito_id'                => $r->deposito_id,
            'deposito_destino_id'        => $nota->tipo === 'TRANSFERENCIA' ? $r->deposito_destino_id : null,
            'marca_id'                   => $r->marca_id  ?: null,
            'modelo_id'                  => $r->modelo_id ?: null,
        ]);

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 200);
    }

    public function update(Request $r, $nota_remi_comp_id)
    {
        $r->validate([
            'item_id'                    => 'required|exists:items,id',
            'nota_remi_com_det_cantidad' => 'required|numeric|min:0.01',
            'deposito_id'                => 'required|exists:deposito,id',
            'deposito_destino_id'        => 'nullable|exists:deposito,id',
        ]);

        $nota = DB::table('nota_remi_comp')->where('id', $nota_remi_comp_id)->first();

        // Depósito origen debe pertenecer a la sucursal origen
        $depOrigen = DB::table('deposito')->where('id', $r->deposito_id)->first();
        if (!$depOrigen || $depOrigen->sucursal_id != $nota->sucursal_id) {
            return response()->json([
                'mensaje' => 'El depósito origen no pertenece a la sucursal origen de la nota.',
                'tipo'    => 'error'
            ], 422);
        }

        if ($nota->tipo === 'TRANSFERENCIA') {
            if (!$r->deposito_destino_id) {
                return response()->json([
                    'mensaje' => 'El depósito destino es obligatorio para transferencias.',
                    'tipo'    => 'error'
                ], 422);
            }

            $depDestino = DB::table('deposito')->where('id', $r->deposito_destino_id)->first();
            if (!$depDestino || $depDestino->sucursal_id != $nota->sucursal_destino_id) {
                return response()->json([
                    'mensaje' => 'El depósito destino no pertenece a la sucursal destino de la nota.',
                    'tipo'    => 'error'
                ], 422);
            }

            if ($r->deposito_id == $r->deposito_destino_id) {
                return response()->json([
                    'mensaje' => 'El depósito origen y el destino no pueden ser el mismo.',
                    'tipo'    => 'error'
                ], 422);
            }
        }

        DB::table('nota_remi_com_det')
            ->where('nota_remi_comp_id', $nota_remi_comp_id)
            ->where('item_id', $r->item_id)
            ->update([
                'nota_remi_com_det_cantidad' => $r->nota_remi_com_det_cantidad,
                'deposito_id'               => $r->deposito_id,
                'deposito_destino_id'       => $nota->tipo === 'TRANSFERENCIA' ? $r->deposito_destino_id : null,
            ]);

        $detalle = DB::select("
            SELECT * FROM nota_remi_com_det
            WHERE nota_remi_comp_id = ? AND item_id = ?
        ", [$nota_remi_comp_id, $r->item_id]);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 200);
    }

    public function destroy($nota_remi_comp_id, $item_id)
    {
        DB::table('nota_remi_com_det')
            ->where('nota_remi_comp_id', $nota_remi_comp_id)
            ->where('item_id', $item_id)
            ->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success',
        ], 200);
    }
}
