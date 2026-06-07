<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompraDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrdenCompraDetController extends Controller
{
    public function read($id) {
        return DB::select("
            SELECT ocd.orden_compra_cab_id, ocd.item_id, ocd.tipo_impuesto_id,
                   ocd.orden_compra_det_cantidad, ocd.orden_compra_det_costo, ocd.deposito_id,
                   ocd.marca_id, ocd.modelo_id,
                   i.item_decripcion, i.item_costo, ti.tip_imp_nom,
                   COALESCE(ma.marc_nom, '')   AS marc_nom,
                   COALESCE(mo.modelo_nom, '') AS modelo_nom,
                   COALESCE(SUM(s.cantidad), 0) AS cantidad_disponible
            FROM orden_compra_det ocd
            JOIN items i          ON i.id  = ocd.item_id
            JOIN tipo_impuesto ti  ON ti.id = ocd.tipo_impuesto_id
            LEFT JOIN marca ma     ON ma.id = ocd.marca_id
            LEFT JOIN modelo mo    ON mo.id = ocd.modelo_id
            LEFT JOIN stock s      ON s.item_id = i.id
            WHERE ocd.orden_compra_cab_id = ?
            GROUP BY ocd.orden_compra_cab_id, ocd.item_id, ocd.tipo_impuesto_id,
                     ocd.orden_compra_det_cantidad, ocd.orden_compra_det_costo, ocd.deposito_id,
                     ocd.marca_id, ocd.modelo_id,
                     i.item_decripcion, i.item_costo, ti.tip_imp_nom, ma.marc_nom, mo.modelo_nom
        ", [$id]);
    }

    public function depositosDeLaOrden($orden_compra_cab_id)
    {
        return DB::select("
            SELECT DISTINCT d.id, d.dep_nombre, s.suc_razon_social
            FROM orden_compra_det ocd
            JOIN deposito  d ON d.id  = ocd.deposito_id
            JOIN sucursal  s ON s.id  = d.sucursal_id
            WHERE ocd.orden_compra_cab_id = ?
              AND ocd.deposito_id IS NOT NULL
            ORDER BY d.id
        ", [$orden_compra_cab_id]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'orden_compra_cab_id'       => 'required|exists:orden_compra_cab,id',
            'item_id'                   => 'required|exists:items,id',
            'tipo_impuesto_id'          => 'required|exists:tipo_impuesto,id',
            'orden_compra_det_cantidad' => 'required|numeric',
            'orden_compra_det_costo'    => 'required|numeric',
            'deposito_id'               => 'nullable|exists:deposito,id',
        ]);

        $deposito = $request->deposito_id ?: null;

        $existente = DB::table('orden_compra_det')
            ->where('orden_compra_cab_id', $request->orden_compra_cab_id)
            ->where('item_id', $request->item_id)
            ->first();

        if ($existente) {
            if ((string)($existente->deposito_id) !== (string)($deposito)) {
                return response()->json([
                    'mensaje' => 'El ítem ya existe en el detalle con un depósito diferente. Modificá el registro existente.',
                    'tipo'    => 'warning'
                ], 422);
            }
            DB::table('orden_compra_det')
                ->where('orden_compra_cab_id', $request->orden_compra_cab_id)
                ->where('item_id', $request->item_id)
                ->update(['orden_compra_det_cantidad' => $existente->orden_compra_det_cantidad + $request->orden_compra_det_cantidad]);

            return response()->json([
                'mensaje' => 'Cantidad sumada al ítem existente',
                'tipo'    => 'success'
            ], 200);
        }

        $detalle = OrdenCompraDet::create([
            'orden_compra_cab_id'       => $request->orden_compra_cab_id,
            'item_id'                   => $request->item_id,
            'tipo_impuesto_id'          => $request->tipo_impuesto_id,
            'orden_compra_det_cantidad' => $request->orden_compra_det_cantidad,
            'orden_compra_det_costo'    => $request->orden_compra_det_costo,
            'deposito_id'               => $deposito,
        ]);

        return response()->json([
            'mensaje' => 'Registro creado con éxito',
            'tipo'    => 'success',
            'registro' => $detalle
        ], 201);
    }

public function update(Request $r, $orden_compra_cab_id, $item_id)
{
    // Validar los datos del request y asignarlos a $datosValidados
    $datosValidados = $r->validate([
        "orden_compra_det_cantidad" => "required|numeric",
        "tipo_impuesto_id"          => "required|exists:tipo_impuesto,id",
        "orden_compra_det_costo"    => "required|numeric",
        "deposito_id"               => "nullable|exists:deposito,id",
    ]);

    $ordencompradet = DB::table('orden_compra_det')
        ->where('orden_compra_cab_id', $orden_compra_cab_id)
        ->where('item_id', $item_id)
        ->update([
            'orden_compra_det_cantidad' => $datosValidados['orden_compra_det_cantidad'],
            'tipo_impuesto_id'          => $datosValidados['tipo_impuesto_id'],
            'orden_compra_det_costo'    => $datosValidados['orden_compra_det_costo'],
            'deposito_id'               => $datosValidados['deposito_id'],
        ]);

    // Verificar si la actualización fue exitosa
    if ($ordencompradet) {
        // Obtener el registro actualizado para retornar en la respuesta
        $ordencompradet = DB::select("select * from orden_compra_det where orden_compra_cab_id = ? and item_id = ?", [$orden_compra_cab_id, $item_id]);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => $ordencompradet
        ], 200);
    } else {
        return response()->json([
            'mensaje' => 'Error al modificar el registro',
            'tipo' => 'error'
        ], 500);
    }
}
    
    public function destroy($orden_compra_cab_id, $item_id){
        $detalle = DB::table('orden_compra_det')->
        where('orden_compra_cab_id', $orden_compra_cab_id)->
        where('item_id', $item_id)->
        delete();

        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
            'registro'=> $detalle
        ],200);
    }
}
