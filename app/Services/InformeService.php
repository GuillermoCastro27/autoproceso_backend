<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class InformeService
{
    public function ejecutar(string $tipo, array $params): array
    {
        $config = config("informes_compras.{$tipo}");

        if (!$config) {
            abort(422, "Tipo de informe no válido: {$tipo}");
        }

        $desde  = $params['desde'];
        $hasta  = $params['hasta'];
        $start  = max(0,   (int)($params['start']  ?? 0));
        $length = min(500, max(1, (int)($params['length'] ?? 500)));
        $ttl    = $config['cache_ttl'] ?? 0;

        $cacheBase = "informe_compras_{$tipo}_{$desde}_{$hasta}";

        $total = $ttl > 0
            ? Cache::remember("{$cacheBase}_total", $ttl,
                fn () => $this->contarRegistros($tipo, $desde, $hasta))
            : $this->contarRegistros($tipo, $desde, $hasta);

        $data = $ttl > 0
            ? Cache::remember("{$cacheBase}_{$start}_{$length}", $ttl,
                fn () => $this->consultarDatos($tipo, $desde, $hasta, $start, $length))
            : $this->consultarDatos($tipo, $desde, $hasta, $start, $length);

        $totales = !empty($config['totales'])
            ? $this->calcularTotales($tipo, $desde, $hasta, $config['totales'])
            : [];

        return [
            'draw'            => (int)($params['draw'] ?? 1),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $data,
            'columnas'        => $config['columnas'],
            'titulo'          => $config['titulo'],
            'totales'         => $totales,
        ];
    }

    // ── Conteos ───────────────────────────────────────────────────────────────

    private function contarRegistros(string $tipo, string $desde, string $hasta): int
    {
        $result = DB::selectOne($this->getSqlCount($tipo), [
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
        return (int)($result->total ?? 0);
    }

    // ── Datos ─────────────────────────────────────────────────────────────────

    private function consultarDatos(string $tipo, string $desde, string $hasta, int $start, int $length): array
    {
        $sql = $this->getSqlData($tipo, $length, $start);
        return DB::select($sql, [
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }

    // ── Totales (solo libro_compras por ahora) ────────────────────────────────

    private function calcularTotales(string $tipo, string $desde, string $hasta, array $defs): array
    {
        $sql    = $this->getSqlTotales($tipo, $defs);
        $result = DB::selectOne($sql, ['desde' => $desde, 'hasta' => $hasta]);
        if (!$result) {
            return [];
        }
        $out = [];
        foreach ($defs as $def) {
            $out[$def['display']] = $result->{$def['label']} ?? 0;
        }
        return $out;
    }

    // ── SQLs de datos ─────────────────────────────────────────────────────────

    private function getSqlData(string $tipo, int $length, int $start): string
    {
        $pg = "LIMIT {$length} OFFSET {$start}";

        return match ($tipo) {

            'pedidos' => "
                SELECT p.id,
                       e.emp_razon_social                          AS empresa,
                       s.suc_razon_social                          AS sucursal,
                       TO_CHAR(p.ped_fecha, 'dd/mm/yyyy')          AS fecha,
                       TO_CHAR(p.ped_vence, 'dd/mm/yyyy')          AS entrega,
                       p.ped_pbservaciones                         AS observaciones,
                       f.fun_nom || ' ' || f.fun_apellido          AS funcionario,
                       p.ped_estado                                AS estado
                FROM pedidos p
                JOIN funcionario f ON f.id = p.funcionario_id
                JOIN sucursal    s ON s.id = p.sucursal_id
                JOIN empresa     e ON e.id = p.empresa_id
                WHERE p.ped_estado = 'PROCESADO'
                  AND p.ped_fecha BETWEEN :desde AND :hasta
                ORDER BY p.ped_fecha ASC
                {$pg}
            ",

            'presupuestos' => "
                SELECT p.id,
                       e.emp_razon_social                          AS empresa,
                       s.suc_razon_social                          AS sucursal,
                       TO_CHAR(p.pre_fecha, 'dd/mm/yyyy')          AS fecha,
                       TO_CHAR(p.pre_vence, 'dd/mm/yyyy')          AS entrega,
                       p.pre_observaciones                         AS observaciones,
                       prov.prov_razonsocial                       AS proveedor,
                       prov.prov_ruc                               AS ruc,
                       f.fun_nom || ' ' || f.fun_apellido          AS funcionario,
                       p.pre_estado                                AS estado,
                       (SELECT STRING_AGG('PEDIDO NRO: ' || TO_CHAR(ped.id, '0000000'), ' | ')
                        FROM presupuesto_pedidos pp
                        JOIN pedidos ped ON ped.id = pp.pedido_id
                        WHERE pp.presupuesto_id = p.id)            AS pedidos
                FROM presupuestos p
                JOIN funcionario f    ON f.id    = p.funcionario_id
                JOIN sucursal    s    ON s.id    = p.sucursal_id
                JOIN empresa     e    ON e.id    = p.empresa_id
                JOIN proveedores prov ON prov.id = p.proveedor_id
                WHERE p.pre_estado = 'PROCESADO'
                  AND p.pre_fecha BETWEEN :desde AND :hasta
                ORDER BY p.pre_fecha ASC
                {$pg}
            ",

            'ordenes_compras' => "
                SELECT o.id,
                       TO_CHAR(o.ord_comp_fecha, 'dd/mm/yyyy')                              AS fecha,
                       prov.prov_razonsocial                                                AS proveedor,
                       prov.prov_ruc                                                        AS ruc,
                       COALESCE(TO_CHAR(o.ord_comp_intervalo_fecha_vence, 'dd/mm/yyyy'), 'N/A') AS entrega,
                       o.ord_comp_estado                                                    AS estado,
                       o.condicion_pago,
                       COALESCE(o.ord_comp_cant_cuota::varchar, '0')                        AS cuotas,
                       f.fun_nom || ' ' || f.fun_apellido                                   AS funcionario,
                       e.emp_razon_social                                                   AS empresa,
                       s.suc_razon_social                                                   AS sucursal,
                       'PRESUPUESTO NRO: ' || TO_CHAR(pr.id, '0000000')
                           || ' VENCE EL: '
                           || COALESCE(TO_CHAR(pr.pre_vence, 'dd/mm/yyyy'), 'N/A')
                           || ' (' || pr.pre_observaciones || ')'                           AS presupuesto
                FROM orden_compra_cab o
                JOIN funcionario f    ON f.id    = o.funcionario_id
                JOIN sucursal    s    ON s.id    = o.sucursal_id
                JOIN empresa     e    ON e.id    = o.empresa_id
                JOIN presupuestos pr  ON pr.id   = o.presupuesto_id
                JOIN proveedores prov ON prov.id = pr.proveedor_id
                WHERE o.ord_comp_estado = 'PROCESADO'
                  AND o.ord_comp_fecha BETWEEN :desde AND :hasta
                ORDER BY o.ord_comp_fecha ASC
                {$pg}
            ",

            'compras' => "
                SELECT cc.id,
                       e.emp_razon_social                                                   AS empresa,
                       s.suc_razon_social                                                   AS sucursal,
                       TO_CHAR(cc.comp_fecha, 'dd/mm/yyyy')                                 AS fecha,
                       CASE WHEN cc.comp_intervalo_fecha_vence IS NOT NULL
                            THEN TO_CHAR(cc.comp_intervalo_fecha_vence, 'dd/mm/yyyy')
                            ELSE 'N/A' END                                                  AS entrega,
                       COALESCE(prov.prov_razonsocial, 'SIN PROVEEDOR')                     AS proveedor,
                       COALESCE(prov.prov_ruc, 'SIN RUC')                                   AS ruc,
                       cc.condicion_pago,
                       COALESCE(cc.comp_cant_cuota::varchar, '0')                           AS cuotas,
                       f.fun_nom || ' ' || f.fun_apellido                                   AS funcionario,
                       cc.comp_estado                                                       AS estado,
                       COALESCE('ORDEN DE COMPRA NRO: ' || TO_CHAR(occ.id, '0000000')
                           || CASE WHEN occ.ord_comp_intervalo_fecha_vence IS NOT NULL
                                   THEN ' VENCE EL: ' || TO_CHAR(occ.ord_comp_intervalo_fecha_vence, 'dd/mm/yyyy')
                                   ELSE ' N/A' END,
                           'SIN ORDEN')                                                     AS ordencompra
                FROM compra_cab cc
                JOIN funcionario f          ON f.id   = cc.funcionario_id
                JOIN sucursal    s          ON s.id   = cc.sucursal_id
                JOIN empresa     e          ON e.id   = cc.empresa_id
                LEFT JOIN orden_compra_cab occ  ON occ.id  = cc.orden_compra_cab_id
                LEFT JOIN presupuestos     p    ON p.id    = occ.presupuesto_id
                LEFT JOIN proveedores      prov ON prov.id = p.proveedor_id
                WHERE cc.comp_estado = 'PROCESADO'
                  AND cc.comp_fecha BETWEEN :desde AND :hasta
                ORDER BY cc.comp_fecha ASC
                {$pg}
            ",

            'libro_compras' => "
                SELECT lc.compra_cab_id                            AS id,
                       TO_CHAR(lc.\"libC_fecha\", 'dd/mm/yyyy')   AS fecha,
                       COALESCE(lc.\"libC_tipo_nota\", 'N/A')     AS tipo_nota,
                       lc.\"prov_razonsocial\"                    AS proveedor,
                       lc.\"prov_ruc\"                            AS ruc,
                       lc.\"condicion_pago\"                      AS condicion_pago,
                       lc.\"tip_imp_nom\"                         AS impuesto,
                       lc.\"libC_monto\"                          AS monto,
                       lc.\"libC_cuota\"                          AS cuota
                FROM libro_compras lc
                WHERE lc.\"libC_fecha\" BETWEEN :desde AND :hasta
                ORDER BY lc.\"libC_fecha\" ASC
                {$pg}
            ",

            'nota_remi_comp' => "
                SELECT nrc.id,
                       TO_CHAR(nrc.nota_remi_fecha, 'dd/mm/yyyy') AS fecha,
                       nrc.nota_remi_observaciones                 AS observaciones,
                       nrc.nota_remi_estado                        AS estado,
                       f.fun_nom || ' ' || f.fun_apellido          AS funcionario,
                       e.emp_razon_social                          AS empresa,
                       s.suc_razon_social                          AS sucursal
                FROM nota_remi_comp nrc
                JOIN funcionario f ON f.id = nrc.funcionario_id
                JOIN sucursal    s ON s.id = nrc.sucursal_id
                JOIN empresa     e ON e.id = nrc.empresa_id
                WHERE nrc.nota_remi_estado = 'CONFIRMADO'
                  AND nrc.nota_remi_fecha BETWEEN :desde AND :hasta
                ORDER BY nrc.nota_remi_fecha ASC
                {$pg}
            ",

            'ajuste_inventario' => "
                SELECT ac.id,
                       TO_CHAR(ac.ajus_cab_fecha, 'dd/mm/yyyy')   AS fecha,
                       ma.descripcion                              AS motivo,
                       ac.tipo_ajuste                              AS tipo,
                       ac.ajus_cab_estado                          AS estado,
                       f.fun_nom || ' ' || f.fun_apellido          AS funcionario,
                       e.emp_razon_social                          AS empresa,
                       s.suc_razon_social                          AS sucursal
                FROM ajuste_cab ac
                JOIN funcionario   f  ON f.id  = ac.funcionario_id
                JOIN sucursal      s  ON s.id  = ac.sucursal_id
                JOIN empresa       e  ON e.id  = ac.empresa_id
                JOIN motivo_ajuste ma ON ma.id = ac.motivo_ajuste_id
                WHERE ac.ajus_cab_estado = 'CONFIRMADO'
                  AND ac.ajus_cab_fecha BETWEEN :desde AND :hasta
                ORDER BY ac.ajus_cab_fecha ASC
                {$pg}
            ",

            'notas_compra' => "
                SELECT ncc.id,
                       e.emp_razon_social                                                   AS empresa,
                       s.suc_razon_social                                                   AS sucursal,
                       TO_CHAR(ncc.nota_comp_fecha, 'dd/mm/yyyy')                           AS fecha,
                       COALESCE(TO_CHAR(ncc.nota_comp_intervalo_fecha_vence, 'dd/mm/yyyy'), 'N/A') AS entrega,
                       ncc.nota_comp_tipo                                                   AS tipo,
                       COALESCE(p.prov_razonsocial, 'SIN PROVEEDOR')                        AS proveedor,
                       COALESCE(p.prov_ruc, 'SIN RUC')                                      AS ruc,
                       ncc.nota_comp_condicion_pago                                         AS condicion_pago,
                       COALESCE(ncc.nota_comp_cant_cuota::varchar, '0')                     AS cuotas,
                       f.fun_nom || ' ' || f.fun_apellido                                   AS funcionario,
                       ncc.nota_comp_estado                                                 AS estado,
                       COALESCE('COMPRA NRO: ' || TO_CHAR(cc.id, '0000000'), 'SIN COMPRA') AS compra
                FROM notas_comp_cab ncc
                JOIN funcionario f      ON f.id   = ncc.funcionario_id
                JOIN sucursal    s      ON s.id   = ncc.sucursal_id
                JOIN empresa     e      ON e.id   = ncc.empresa_id
                LEFT JOIN compra_cab cc ON cc.id  = ncc.compra_cab_id
                LEFT JOIN proveedores p ON p.id   = cc.proveedor_id
                WHERE ncc.nota_comp_estado = 'CONFIRMADO'
                  AND ncc.nota_comp_fecha BETWEEN :desde AND :hasta
                ORDER BY ncc.nota_comp_fecha ASC
                {$pg}
            ",

            default => throw new \InvalidArgumentException("SQL no definido para: {$tipo}"),
        };
    }

    // ── SQLs de conteo ────────────────────────────────────────────────────────

    private function getSqlCount(string $tipo): string
    {
        return match ($tipo) {
            'pedidos'          => "SELECT COUNT(*) AS total FROM pedidos
                                   WHERE ped_estado = 'PROCESADO'
                                     AND ped_fecha BETWEEN :desde AND :hasta",

            'presupuestos'     => "SELECT COUNT(*) AS total FROM presupuestos
                                   WHERE pre_estado = 'PROCESADO'
                                     AND pre_fecha BETWEEN :desde AND :hasta",

            'ordenes_compras'  => "SELECT COUNT(*) AS total FROM orden_compra_cab
                                   WHERE ord_comp_estado = 'PROCESADO'
                                     AND ord_comp_fecha BETWEEN :desde AND :hasta",

            'compras'          => "SELECT COUNT(*) AS total FROM compra_cab
                                   WHERE comp_estado = 'PROCESADO'
                                     AND comp_fecha BETWEEN :desde AND :hasta",

            'libro_compras'    => "SELECT COUNT(*) AS total FROM libro_compras
                                   WHERE \"libC_fecha\" BETWEEN :desde AND :hasta",

            'nota_remi_comp'   => "SELECT COUNT(*) AS total FROM nota_remi_comp
                                   WHERE nota_remi_estado = 'CONFIRMADO'
                                     AND nota_remi_fecha BETWEEN :desde AND :hasta",

            'ajuste_inventario' => "SELECT COUNT(*) AS total FROM ajuste_cab
                                    WHERE ajus_cab_estado = 'CONFIRMADO'
                                      AND ajus_cab_fecha BETWEEN :desde AND :hasta",

            'notas_compra'     => "SELECT COUNT(*) AS total FROM notas_comp_cab
                                   WHERE nota_comp_estado = 'CONFIRMADO'
                                     AND nota_comp_fecha BETWEEN :desde AND :hasta",

            default => throw new \InvalidArgumentException("SQL COUNT no definido para: {$tipo}"),
        };
    }

    // ── SQL de totales ────────────────────────────────────────────────────────

    private function getSqlTotales(string $tipo, array $defs): string
    {
        if ($tipo === 'libro_compras') {
            return "SELECT SUM(\"libC_monto\") AS total_monto, COUNT(*) AS registros
                    FROM libro_compras
                    WHERE \"libC_fecha\" BETWEEN :desde AND :hasta";
        }

        $selects = collect($defs)
            ->map(fn ($d) => "{$d['query']} AS {$d['label']}")
            ->implode(', ');

        return "SELECT {$selects} FROM (SELECT 1) t WHERE FALSE";
    }
}
