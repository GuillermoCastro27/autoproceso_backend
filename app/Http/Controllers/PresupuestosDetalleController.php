<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PresupuestosDetalle;
use Illuminate\Support\Facades\DB;

class PresupuestosDetalleController extends Controller
{
    public function read($id){
        return DB::select("
            SELECT pd.presupuesto_id, pd.item_id, pd.det_cantidad, pd.det_costo, pd.deposito_id,
                   pd.marca_id, pd.modelo_id,
                   i.item_decripcion,
                   COALESCE(ti.tip_imp_nom, 'IVA 10%') AS tip_imp_nom,
                   COALESCE(ma.marc_nom, '')     AS marc_nom,
                   COALESCE(mo.modelo_nom, '')   AS modelo_nom,
                   COALESCE(SUM(s.cantidad), 0)  AS cantidad_disponible
            FROM presupuestos_detalles pd
            JOIN items i               ON i.id  = pd.item_id
            LEFT JOIN tipo_impuesto ti ON ti.id = i.tipo_impuesto_id
            LEFT JOIN marca ma         ON ma.id = pd.marca_id
            LEFT JOIN modelo mo        ON mo.id = pd.modelo_id
            LEFT JOIN stock s          ON s.item_id = i.id
            WHERE pd.presupuesto_id = ?
            GROUP BY pd.presupuesto_id, pd.item_id, pd.det_cantidad, pd.det_costo, pd.deposito_id,
                     pd.marca_id, pd.modelo_id, i.item_decripcion, ti.tip_imp_nom, ma.marc_nom, mo.modelo_nom
        ", [$id]);
    }

    // Depósitos válidos según las sucursales de los pedidos vinculados al presupuesto
    public function depositosPorPedidos($presupuesto_id)
    {
        return DB::select("
            SELECT DISTINCT d.id, d.dep_nombre, s.id AS sucursal_id, s.suc_razon_social
            FROM presupuesto_pedidos pp
            JOIN pedidos_ventas pv ON pv.id = pp.pedido_id
            JOIN deposito d        ON d.sucursal_id = pv.sucursal_id
            JOIN sucursal s        ON s.id = pv.sucursal_id
            WHERE pp.presupuesto_id = ?
            ORDER BY s.id, d.id
        ", [$presupuesto_id]);
    }

    public function depositosDelPresupuesto($presupuesto_id)
    {
        return DB::select("
            SELECT DISTINCT d.id, d.dep_nombre, s.suc_razon_social
            FROM presupuestos_detalles pd
            JOIN deposito  d ON d.id = pd.deposito_id
            JOIN sucursal  s ON s.id = d.sucursal_id
            WHERE pd.presupuesto_id = ?
              AND pd.deposito_id IS NOT NULL
            ORDER BY d.id
        ", [$presupuesto_id]);
    }

    public function store(Request $r){
        $costo = floatval(str_replace(',', '.', str_replace('.', '', $r->det_costo)));

        $r->validate([
            "presupuesto_id" => "required",
            "item_id"        => "required",
            "det_cantidad"   => "required|numeric",
            "deposito_id"    => "nullable|exists:deposito,id",
        ]);

        $deposito = $r->deposito_id ?: null;

        $existente = DB::table('presupuestos_detalles')
            ->where('presupuesto_id', $r->presupuesto_id)
            ->where('item_id', $r->item_id)
            ->first();

        if ($existente) {
            if ((string)($existente->deposito_id) !== (string)($deposito)) {
                return response()->json([
                    'mensaje' => 'El ítem ya existe en el detalle con un depósito diferente. Modificá el registro existente.',
                    'tipo'    => 'warning'
                ], 422);
            }
            DB::table('presupuestos_detalles')
                ->where('presupuesto_id', $r->presupuesto_id)
                ->where('item_id', $r->item_id)
                ->update(['det_cantidad' => $existente->det_cantidad + $r->det_cantidad]);

            return response()->json([
                'mensaje' => 'Cantidad sumada al ítem existente',
                'tipo'    => 'success'
            ], 200);
        }

        $detalle = PresupuestosDetalle::create([
            'presupuesto_id' => $r->presupuesto_id,
            'item_id'        => $r->item_id,
            'det_cantidad'   => $r->det_cantidad,
            'det_costo'      => $costo,
            'deposito_id'    => $deposito,
            'marca_id'       => $r->marca_id  ?: null,
            'modelo_id'      => $r->modelo_id ?: null,
        ]);
        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $detalle
        ], 200);
    }
    public function update(Request $r, $presupuesto_id, $item_id)
    {
        $costo = str_replace('.', '', $r->det_costo);
        $costo = str_replace(',', '.', $costo);
        $costo = floatval($costo);

        $query = DB::table('presupuestos_detalles')
            ->where('presupuesto_id', $presupuesto_id)
            ->where('item_id', $item_id);

        // Filtrar por el depósito original para no afectar otras filas del mismo ítem
        if ($r->deposito_id_original) {
            $query->where('deposito_id', $r->deposito_id_original);
        } else {
            $query->whereNull('deposito_id');
        }

        $query->update([
            'det_costo'    => $costo,
            'det_cantidad' => $r->det_cantidad,
            'deposito_id'  => $r->deposito_id ?: null,
            'marca_id'     => $r->marca_id  ?: null,
            'modelo_id'    => $r->modelo_id ?: null,
        ]);

        // Traer el registro actualizado
        $detalle = DB::select("
            SELECT * 
            FROM presupuestos_detalles 
            WHERE presupuesto_id = ? AND item_id = ?
        ", [$presupuesto_id, $item_id]);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo'    => 'success',
            'registro'=> $detalle
        ], 200);
    }
    public function destroy(Request $r, $presupuesto_id, $item_id){
        $query = DB::table('presupuestos_detalles')
            ->where('presupuesto_id', $presupuesto_id)
            ->where('item_id', $item_id);

        // Eliminar solo la fila con el depósito específico si viene informado
        if ($r->deposito_id_original) {
            $query->where('deposito_id', $r->deposito_id_original);
        } elseif ($r->has('deposito_id_original')) {
            $query->whereNull('deposito_id');
        }

        $detalle = $query->delete();

        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
            'registro'=> $detalle
        ],200);
    }
}
