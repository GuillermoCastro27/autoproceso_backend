<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Presupuesto;
use App\Models\PresupuestoPedido;
use App\Models\PresupuestosDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresupuestoController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                p.*,
                TO_CHAR(p.pre_fecha, 'dd/mm/yyyy HH24:mi:ss') AS pre_fecha,
                TO_CHAR(p.pre_vence, 'dd/mm/yyyy HH24:mi:ss') AS pre_vence,
                p2.prov_razonsocial,
                p2.prov_ruc,
                p2.prov_telefono,
                p2.prov_correo,
                s.suc_razon_social,
                e.emp_razon_social,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                (
                    SELECT STRING_AGG(
                        'PEDIDO NRO: ' || TO_CHAR(ped.id, '0000000') ||
                        ' VENCE: ' || TO_CHAR(ped.ped_vence, 'dd/mm/yyyy') ||
                        ' (' || ped.ped_pbservaciones || ')',
                        ' | '
                    )
                    FROM presupuesto_pedidos pp
                    JOIN pedidos ped ON ped.id = pp.pedido_id
                    WHERE pp.presupuesto_id = p.id
                ) AS pedidos
            FROM presupuestos p
            JOIN proveedores p2 ON p2.id = p.proveedor_id
            JOIN sucursal s     ON s.id = p.sucursal_id
            JOIN empresa e      ON e.id = p.empresa_id
            JOIN funcionario f  ON f.id = p.funcionario_id
            ORDER BY p.id DESC
        ");
    }

    public function store(Request $r)
    {
        $r->validate([
            'pre_observaciones' => 'required',
            'pre_estado'        => 'required',
            'pre_fecha'         => 'required',
            'pre_vence'         => 'required',
            'proveedor_id'      => 'required',
            'pedidos_ids'       => 'required',
            'empresa_id'        => 'required',
            'sucursal_id'       => 'required',
        ]);

        $pedidoIds = json_decode($r->pedidos_ids, true);

        if (empty($pedidoIds) || !is_array($pedidoIds)) {
            return response()->json([
                'mensaje' => 'Debe seleccionar al menos un pedido',
                'tipo'    => 'error'
            ], 422);
        }

        $presupuesto = Presupuesto::create([
            'pre_observaciones' => $r->pre_observaciones,
            'pre_estado'        => $r->pre_estado,
            'pre_fecha'         => $r->pre_fecha,
            'pre_vence'         => $r->pre_vence,
            'proveedor_id'      => $r->proveedor_id,
            'funcionario_id'    => auth()->user()->funcionario_id,
            'empresa_id'        => $r->empresa_id,
            'sucursal_id'       => $r->sucursal_id,
        ]);

        $itemsAcumulados = [];

        foreach ($pedidoIds as $pedidoId) {
            PresupuestoPedido::create([
                'presupuesto_id'                => $presupuesto->id,
                'pedido_id'                     => $pedidoId,
                'pres_prov_ped_fecha_registro'  => now(),
            ]);

            $pedido = Pedido::find($pedidoId);
            $pedido->ped_estado = 'PROCESADO';
            $pedido->save();

            $detalles = DB::select("
                SELECT pd.*, pd.deposito_id, i.item_costo
                FROM pedidos_detalles pd
                JOIN items i ON i.id = pd.item_id
                WHERE pd.pedidos_id = ?
            ", [$pedidoId]);

            foreach ($detalles as $dp) {
                $key = $dp->item_id . '_' . ($dp->deposito_id ?? 'null');
                if (isset($itemsAcumulados[$key])) {
                    $itemsAcumulados[$key]['det_cantidad'] += $dp->det_cantidad;
                } else {
                    $itemsAcumulados[$key] = [
                        'item_id'      => $dp->item_id,
                        'deposito_id'  => $dp->deposito_id,
                        'det_costo'    => $dp->item_costo,
                        'det_cantidad' => $dp->det_cantidad,
                    ];
                }
            }
        }

        foreach ($itemsAcumulados as $item) {
            $detalle = new PresupuestosDetalle();
            $detalle->presupuesto_id = $presupuesto->id;
            $detalle->item_id        = $item['item_id'];
            $detalle->deposito_id    = $item['deposito_id'];
            $detalle->det_costo      = $item['det_costo'];
            $detalle->det_cantidad   = $item['det_cantidad'];
            $detalle->save();
        }

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $presupuesto
        ]);
    }

    public function update(Request $r, $id)
    {
        $presupuesto = Presupuesto::find($id);
        if (!$presupuesto) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $r->validate([
            'pre_observaciones' => 'required',
            'pre_estado'        => 'required',
            'pre_fecha'         => 'required',
            'pre_vence'         => 'required',
            'proveedor_id'      => 'required',
            'empresa_id'        => 'required',
            'sucursal_id'       => 'required',
        ]);

        $presupuesto->update($r->only([
            'pre_observaciones', 'pre_estado', 'pre_fecha',
            'pre_vence', 'proveedor_id', 'empresa_id', 'sucursal_id'
        ]));

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $presupuesto
        ]);
    }

    public function anular(Request $r, $id)
    {
        $presupuesto = Presupuesto::find($id);
        if (!$presupuesto) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $r->validate(['pre_observaciones' => 'required']);

        $presupuesto->pre_estado        = 'ANULADO';
        $presupuesto->pre_observaciones = $r->pre_observaciones;
        $presupuesto->save();

        // Revertir todos los pedidos vinculados a CONFIRMADO
        $vinculados = PresupuestoPedido::where('presupuesto_id', $id)->get();
        foreach ($vinculados as $vínculo) {
            $pedido = Pedido::find($vínculo->pedido_id);
            if ($pedido) {
                $pedido->ped_estado = 'CONFIRMADO';
                $pedido->save();
            }
        }

        return response()->json([
            'mensaje'  => 'Presupuesto anulado con éxito',
            'tipo'     => 'success',
            'registro' => $presupuesto
        ]);
    }

    public function confirmar(Request $r, $id)
    {
        $presupuesto = Presupuesto::find($id);
        if (!$presupuesto) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $r->validate([
            'pre_observaciones' => 'required',
            'pre_estado'        => 'required',
            'pre_fecha'         => 'required',
            'pre_vence'         => 'required',
            'proveedor_id'      => 'required',
            'empresa_id'        => 'required',
            'sucursal_id'       => 'required',
        ]);

        $presupuesto->update($r->only([
            'pre_observaciones', 'pre_estado', 'pre_fecha',
            'pre_vence', 'proveedor_id', 'empresa_id', 'sucursal_id'
        ]));

        return response()->json([
            'mensaje'  => 'Registro confirmado con éxito',
            'tipo'     => 'success',
            'registro' => $presupuesto
        ]);
    }

    public function buscar(Request $r)
    {
        return DB::select("
            SELECT
                p.id AS presupuesto_id,
                TO_CHAR(p.pre_vence, 'dd/mm/yyyy HH24:mi:ss') AS pre_vence,
                p.pre_observaciones,
                p.pre_estado,
                p.funcionario_id,
                p.sucursal_id,
                s.suc_razon_social,
                p.empresa_id,
                e.emp_razon_social,
                p.created_at,
                p.updated_at,
                f.fun_nom || ' ' || f.fun_apellido AS encargado,
                p.proveedor_id,
                prov.prov_razonsocial,
                prov.prov_ruc,
                prov.prov_telefono,
                prov.prov_correo,
                'PRESUPUESTO NRO: ' || TO_CHAR(p.id, '0000000') ||
                ' VENCE EL: ' || TO_CHAR(p.pre_vence, 'dd/mm/yyyy HH24:mi:ss') ||
                ' (' || p.pre_observaciones || ')' AS presupuesto
            FROM presupuestos p
            JOIN funcionario f   ON f.id = p.funcionario_id
            JOIN sucursal s      ON s.id = p.sucursal_id
            JOIN empresa e       ON e.id = p.empresa_id
            JOIN proveedores prov ON prov.id = p.proveedor_id
            WHERE p.pre_estado = 'CONFIRMADO'
              AND p.funcionario_id = ?
        ", [$r->funcionario_id]);
    }

    public function readPedidos($presupuesto_id)
    {
        return DB::select("
            SELECT
                pp.id,
                pp.pedido_id,
                pp.pres_prov_ped_fecha_registro,
                TO_CHAR(p.ped_fecha, 'dd/mm/yyyy') AS ped_fecha,
                TO_CHAR(p.ped_vence, 'dd/mm/yyyy') AS ped_vence,
                p.ped_pbservaciones,
                p.ped_estado
            FROM presupuesto_pedidos pp
            JOIN pedidos p ON p.id = pp.pedido_id
            WHERE pp.presupuesto_id = ?
            ORDER BY pp.id
        ", [$presupuesto_id]);
    }

    public function buscarInforme(Request $r)
    {
        return DB::select("
            SELECT
                p.id,
                TO_CHAR(p.pre_fecha, 'dd/mm/yyyy') AS fecha,
                TO_CHAR(p.pre_vence, 'dd/mm/yyyy') AS entrega,
                p.pre_observaciones AS observaciones,
                p.pre_estado AS estado,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                s.suc_razon_social AS sucursal,
                e.emp_razon_social AS empresa,
                prov.prov_razonsocial AS proveedor,
                prov.prov_ruc AS ruc,
                (
                    SELECT STRING_AGG('PEDIDO NRO: ' || TO_CHAR(ped.id, '0000000'), ' | ')
                    FROM presupuesto_pedidos pp
                    JOIN pedidos ped ON ped.id = pp.pedido_id
                    WHERE pp.presupuesto_id = p.id
                ) AS pedidos
            FROM presupuestos p
            JOIN funcionario f    ON f.id = p.funcionario_id
            JOIN sucursal s       ON s.id = p.sucursal_id
            JOIN empresa e        ON e.id = p.empresa_id
            JOIN proveedores prov ON prov.id = p.proveedor_id
            WHERE p.pre_estado = 'PROCESADO'
              AND p.pre_fecha BETWEEN ? AND ?
            ORDER BY p.pre_fecha ASC
        ", [$r->query('desde'), $r->query('hasta')]);
    }
}
