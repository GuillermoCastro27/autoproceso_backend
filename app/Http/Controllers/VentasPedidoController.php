<?php

namespace App\Http\Controllers;

use App\Models\VentasPedido;
use App\Models\VentasDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentasPedidoController extends Controller
{
    /**
     * Vincula un pedido de venta a una venta y copia sus detalles a ventas_det.
     */
    public function store(Request $r)
    {
        $r->validate([
            'ventas_cab_id'     => 'required|integer|exists:ventas_cab,id',
            'pedidos_ventas_id' => 'required|integer|exists:pedidos_ventas,id',
        ]);

        $ventaCabId    = (int) $r->ventas_cab_id;
        $pedidoVentaId = (int) $r->pedidos_ventas_id;

        // Solo se vincula a ventas PENDIENTE
        $venta = DB::table('ventas_cab')->where('id', $ventaCabId)->first();
        if (!$venta || $venta->vent_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden vincular pedidos a ventas en estado PENDIENTE.',
                'tipo'    => 'error',
            ], 422);
        }

        // No duplicar el vínculo
        $existe = DB::table('ventas_pedidos')
            ->where('ventas_cab_id', $ventaCabId)
            ->where('pedidos_ventas_id', $pedidoVentaId)
            ->exists();
        if ($existe) {
            return response()->json([
                'mensaje' => 'Este pedido ya está vinculado a la venta.',
                'tipo'    => 'warning',
            ], 409);
        }

        // El pedido debe estar CONFIRMADO
        $pedido = DB::table('pedidos_ventas')->where('id', $pedidoVentaId)->first();
        if (!$pedido || $pedido->ped_ven_estado !== 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden vincular pedidos en estado CONFIRMADO.',
                'tipo'    => 'error',
            ], 422);
        }

        // Crear vínculo
        VentasPedido::create([
            'ventas_cab_id'     => $ventaCabId,
            'pedidos_ventas_id' => $pedidoVentaId,
        ]);

        // Marcar pedido como PROCESADO
        DB::table('pedidos_ventas')
            ->where('id', $pedidoVentaId)
            ->update(['ped_ven_estado' => 'PROCESADO', 'updated_at' => now()]);

        // Copiar detalles del pedido a ventas_det
        $detalles = DB::select("
            SELECT pvd.item_id, pvd.det_cantidad, pvd.deposito_id,
                   i.item_precio, i.tipo_impuesto_id
            FROM pedidos_ventas_det pvd
            JOIN items i ON i.id = pvd.item_id
            WHERE pvd.pedidos_ventas_id = ?
        ", [$pedidoVentaId]);

        foreach ($detalles as $det) {
            VentasDet::create([
                'ventas_cab_id'     => $ventaCabId,
                'item_id'           => $det->item_id,
                'vent_det_cantidad' => $det->det_cantidad,
                'vent_det_precio'   => $det->item_precio,
                'tipo_impuesto_id'  => $det->tipo_impuesto_id,
                'deposito_id'       => $det->deposito_id ?? null,
            ]);
        }

        return response()->json([
            'mensaje' => 'Pedido vinculado y detalles copiados a la venta.',
            'tipo'    => 'success',
        ], 201);
    }

    /**
     * Lista los pedidos vinculados a una venta.
     * Incluye los legacy (pedidos_ventas_id directo en ventas_cab) cuando no hay registros en la pivot.
     */
    public function readByVenta($ventas_cab_id)
    {
        $rows = DB::select("
            SELECT
                vp.id,
                vp.ventas_cab_id,
                vp.pedidos_ventas_id,
                'PED NRO: ' || TO_CHAR(pv.id, '0000000') AS pedido_descripcion,
                pv.ped_ven_estado,
                pv.ped_ven_fecha,
                c.cli_nombre,
                c.cli_apellido
            FROM ventas_pedidos vp
            JOIN pedidos_ventas pv ON pv.id = vp.pedidos_ventas_id
            JOIN clientes c        ON c.id  = pv.clientes_id
            WHERE vp.ventas_cab_id = ?

            UNION ALL

            -- Pedido legacy (col directa en ventas_cab) cuando no hay registros pivot
            SELECT
                0 AS id,
                vc.id AS ventas_cab_id,
                vc.pedidos_ventas_id,
                'PED NRO: ' || TO_CHAR(pv.id, '0000000') AS pedido_descripcion,
                pv.ped_ven_estado,
                pv.ped_ven_fecha,
                c.cli_nombre,
                c.cli_apellido
            FROM ventas_cab vc
            JOIN pedidos_ventas pv ON pv.id = vc.pedidos_ventas_id
            JOIN clientes c        ON c.id  = pv.clientes_id
            WHERE vc.id = ?
              AND vc.pedidos_ventas_id IS NOT NULL
              AND NOT EXISTS (
                  SELECT 1 FROM ventas_pedidos WHERE ventas_cab_id = vc.id
              )

            ORDER BY id ASC
        ", [$ventas_cab_id, $ventas_cab_id]);

        return response()->json($rows);
    }

    /**
     * Elimina el vínculo de un pedido con la venta y revierte el pedido a CONFIRMADO.
     * Nota: los detalles ya copiados a ventas_det no se eliminan automáticamente.
     */
    public function destroy($id)
    {
        $registro = VentasPedido::find($id);

        if (!$registro) {
            return response()->json(['mensaje' => 'Registro no encontrado.', 'tipo' => 'error'], 404);
        }

        // Verificar que la venta siga PENDIENTE
        $venta = DB::table('ventas_cab')->where('id', $registro->ventas_cab_id)->first();
        if ($venta && $venta->vent_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'No se puede desvincular un pedido de una venta que no está PENDIENTE.',
                'tipo'    => 'error',
            ], 422);
        }

        // Revertir pedido a CONFIRMADO
        DB::table('pedidos_ventas')
            ->where('id', $registro->pedidos_ventas_id)
            ->update(['ped_ven_estado' => 'CONFIRMADO', 'updated_at' => now()]);

        $registro->delete();

        return response()->json([
            'mensaje' => 'Vínculo eliminado. El pedido fue revertido a CONFIRMADO. Revise los ítems del detalle de la venta.',
            'tipo'    => 'success',
        ], 200);
    }
}
