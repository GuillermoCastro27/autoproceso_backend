<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function resumen()
    {
        $ventas = DB::selectOne("
            SELECT
                COALESCE(SUM(CASE
                    WHEN DATE_TRUNC('month', v.vent_fecha) = DATE_TRUNC('month', CURRENT_DATE)
                    THEN vd.vent_det_cantidad * vd.vent_det_precio ELSE 0 END), 0) AS mes_actual,
                COALESCE(SUM(CASE
                    WHEN DATE_TRUNC('month', v.vent_fecha) = DATE_TRUNC('month', CURRENT_DATE) - INTERVAL '1 month'
                    THEN vd.vent_det_cantidad * vd.vent_det_precio ELSE 0 END), 0) AS mes_anterior
            FROM ventas_cab v
            JOIN ventas_det vd ON vd.ventas_cab_id = v.id
            WHERE v.vent_fecha >= DATE_TRUNC('month', CURRENT_DATE) - INTERVAL '1 month'
        ");

        $pedidos = DB::selectOne("
            SELECT COUNT(*) AS total FROM pedidos WHERE ped_estado = 'CONFIRMADO'
        ");

        $stock = DB::selectOne("
            SELECT COUNT(DISTINCT item_id) AS total
            FROM stock
            WHERE cantidad_minima > 0 AND cantidad < cantidad_minima
        ");

        $reclamos = DB::selectOne("
            SELECT COUNT(*) AS total
            FROM reclamo_cli_cab
            WHERE rec_cli_cab_estado IN ('PENDIENTE', 'EN PROCESO')
        ");

        $presupuestos = DB::selectOne("
            SELECT COUNT(*) AS total
            FROM presupuestos
            WHERE pre_estado = 'CONFIRMADO'
              AND pre_fecha::date <= CURRENT_DATE - INTERVAL '15 days'
        ");

        return response()->json([
            'ventas_mes_actual'   => (float) ($ventas->mes_actual   ?? 0),
            'ventas_mes_anterior' => (float) ($ventas->mes_anterior  ?? 0),
            'pedidos_pendientes'  => (int)   ($pedidos->total        ?? 0),
            'stock_critico'       => (int)   ($stock->total          ?? 0),
            'reclamos_abiertos'   => (int)   ($reclamos->total       ?? 0),
            'presupuestos_viejos' => (int)   ($presupuestos->total   ?? 0),
        ]);
    }

    public function ventasPorMes()
    {
        $rows = DB::select("
            SELECT
                TO_CHAR(v.vent_fecha, 'YYYY-MM')        AS mes_key,
                EXTRACT(MONTH FROM v.vent_fecha)::int   AS mes_num,
                EXTRACT(YEAR  FROM v.vent_fecha)::int   AS anio,
                COALESCE(SUM(vd.vent_det_cantidad * vd.vent_det_precio), 0) AS total
            FROM ventas_cab v
            JOIN ventas_det vd ON vd.ventas_cab_id = v.id
            WHERE v.vent_fecha >= DATE_TRUNC('month', CURRENT_DATE) - INTERVAL '5 months'
            GROUP BY TO_CHAR(v.vent_fecha, 'YYYY-MM'),
                     EXTRACT(MONTH FROM v.vent_fecha),
                     EXTRACT(YEAR  FROM v.vent_fecha)
            ORDER BY mes_key
        ");

        return response()->json($rows);
    }

    public function topProductos()
    {
        $rows = DB::select("
            SELECT
                i.item_decripcion               AS producto,
                SUM(vd.vent_det_cantidad)::int  AS total_vendido
            FROM ventas_det vd
            JOIN items      i  ON i.id  = vd.item_id
            JOIN ventas_cab v  ON v.id  = vd.ventas_cab_id
            WHERE v.vent_fecha >= CURRENT_DATE - INTERVAL '6 months'
            GROUP BY i.id, i.item_decripcion
            ORDER BY total_vendido DESC
            LIMIT 5
        ");

        return response()->json($rows);
    }

    public function presupuestosDetalle()
    {
        $rows = DB::select("
            SELECT
                p.id,
                pr.prov_razonsocial                              AS proveedor,
                TO_CHAR(p.pre_fecha::date, 'DD/MM/YYYY')        AS fecha,
                (CURRENT_DATE - p.pre_fecha::date)::int         AS dias
            FROM presupuestos p
            JOIN proveedores pr ON pr.id = p.proveedor_id
            WHERE p.pre_estado = 'CONFIRMADO'
              AND p.pre_fecha::date <= CURRENT_DATE - INTERVAL '15 days'
            ORDER BY p.pre_fecha ASC
            LIMIT 8
        ");

        return response()->json($rows);
    }

    public function ventasPorSucursal()
    {
        $rows = DB::select("
            SELECT
                s.suc_razon_social AS sucursal,
                COALESCE(SUM(vd.vent_det_cantidad * vd.vent_det_precio), 0) AS total
            FROM ventas_cab v
            JOIN ventas_det vd ON vd.ventas_cab_id = v.id
            JOIN sucursal   s  ON s.id = v.sucursal_id
            WHERE v.vent_fecha >= CURRENT_DATE - INTERVAL '6 months'
            GROUP BY s.id, s.suc_razon_social
            ORDER BY total DESC
        ");

        return response()->json($rows);
    }
}
