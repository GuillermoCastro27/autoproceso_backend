<?php

namespace App\Http\Controllers;

use App\Models\OrdenServVenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrdenServVentaController extends Controller
{
    public function store(Request $r)
    {
        $r->validate([
            'ventas_cab_id'        => 'required|integer|exists:ventas_cab,id',
            'orden_serv_cab_id'    => 'required|integer|exists:orden_serv_cab,id',
            'contrato_serv_cab_id' => 'nullable|integer|exists:contrato_serv_cab,id',
        ]);

        $registro = OrdenServVenta::create([
            'ventas_cab_id'        => $r->ventas_cab_id,
            'orden_serv_cab_id'    => $r->orden_serv_cab_id,
            'contrato_serv_cab_id' => $r->contrato_serv_cab_id ?: null,
        ]);

        return response()->json([
            'mensaje'   => 'Orden de servicio vinculada a la venta',
            'tipo'      => 'success',
            'registro'  => $registro,
        ], 201);
    }

    public function readByVenta($ventas_cab_id)
    {
        $rows = DB::select("
            SELECT
                osv.id,
                osv.ventas_cab_id,

                -- Orden de servicio
                osv.orden_serv_cab_id,
                'ORDEN NRO: ' || TO_CHAR(osc.id, '0000000')
                    || ' (' || COALESCE(osc.ord_serv_observaciones, 'S/N') || ')' AS orden_descripcion,
                osc.ord_serv_estado,
                osc.ord_serv_tipo,

                -- Cliente de la orden
                c.id   AS clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,

                -- Contrato (opcional)
                osv.contrato_serv_cab_id,
                CASE
                    WHEN osv.contrato_serv_cab_id IS NULL THEN 'Sin contrato'
                    ELSE 'CONTRATO NRO: ' || TO_CHAR(csc.id, '0000000')
                END AS contrato_descripcion

            FROM orden_serv_venta osv
            JOIN orden_serv_cab osc  ON osc.id = osv.orden_serv_cab_id
            JOIN clientes c          ON c.id   = osc.clientes_id
            LEFT JOIN contrato_serv_cab csc ON csc.id = osv.contrato_serv_cab_id

            WHERE osv.ventas_cab_id = ?
            ORDER BY osv.id ASC
        ", [$ventas_cab_id]);

        return response()->json($rows);
    }

    public function destroy($id)
    {
        $registro = OrdenServVenta::find($id);

        if (!$registro) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $registro->delete();

        return response()->json(['mensaje' => 'Vínculo eliminado', 'tipo' => 'success'], 200);
    }

    public function buscarOrdenes(Request $r)
    {
        $texto      = $r->get('q', '');
        $clientesId = $r->get('clientes_id');
        $whereCliente = $clientesId ? 'AND osc.clientes_id = ' . (int)$clientesId : '';

        return DB::select("
            SELECT
                osc.id AS orden_serv_cab_id,
                'ORDEN NRO: ' || TO_CHAR(osc.id, '0000000')
                    || ' (' || COALESCE(osc.ord_serv_observaciones, 'S/N') || ')' AS orden_descripcion,
                osc.ord_serv_estado,
                osc.ord_serv_tipo,

                -- Cliente
                c.id   AS clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                c.cli_correo,

                -- Empresa / Sucursal
                e.id AS empresa_id,
                e.emp_razon_social,
                s.id AS sucursal_id,
                s.suc_razon_social,

                -- Contrato vinculado (nullable)
                csc.id AS contrato_serv_cab_id,
                CASE
                    WHEN csc.id IS NULL THEN NULL
                    ELSE 'CONTRATO NRO: ' || TO_CHAR(csc.id, '0000000')
                END AS contrato_descripcion

            FROM orden_serv_cab osc
            JOIN clientes c  ON c.id = osc.clientes_id
            JOIN empresa e   ON e.id = osc.empresa_id
            JOIN sucursal s  ON s.id = osc.sucursal_id
            LEFT JOIN contrato_serv_cab csc ON csc.orden_serv_cab_id = osc.id
                AND csc.contrato_estado != 'ANULADO'

            WHERE osc.ord_serv_estado = 'CONFIRMADO'
            $whereCliente
            AND (
                TO_CHAR(osc.id, '0000000') ILIKE ?
                OR c.cli_nombre ILIKE ?
                OR c.cli_apellido ILIKE ?
                OR c.cli_ruc ILIKE ?
                OR osc.ord_serv_observaciones ILIKE ?
            )

            ORDER BY osc.id DESC
            LIMIT 10
        ", [
            "%$texto%",
            "%$texto%",
            "%$texto%",
            "%$texto%",
            "%$texto%",
        ]);
    }
}
