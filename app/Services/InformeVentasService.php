<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class InformeVentasService
{
    public function ejecutar(string $tipo, array $params): array
    {
        $config = config("informes_ventas.{$tipo}");

        if (!$config) {
            abort(422, "Tipo de informe no válido: {$tipo}");
        }

        $desde  = $params['desde'];
        $hasta  = $params['hasta'];
        $start  = max(0,   (int)($params['start']  ?? 0));
        $length = min(500, max(1, (int)($params['length'] ?? 500)));
        $ttl    = $config['cache_ttl'] ?? 0;

        $cacheBase = "informe_ventas_{$tipo}_{$desde}_{$hasta}";

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

    private function contarRegistros(string $tipo, string $desde, string $hasta): int
    {
        $result = DB::selectOne($this->getSqlCount($tipo), [
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
        return (int)($result->total ?? 0);
    }

    private function consultarDatos(string $tipo, string $desde, string $hasta, int $start, int $length): array
    {
        $sql = $this->getSqlData($tipo, $length, $start);
        return DB::select($sql, [
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }

    private function calcularTotales(string $tipo, string $desde, string $hasta, array $defs): array
    {
        $sql    = $this->getSqlTotales($tipo);
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

    private function getSqlData(string $tipo, int $length, int $start): string
    {
        $pg = "LIMIT {$length} OFFSET {$start}";

        return match ($tipo) {

            'ventas' => "
                SELECT v.id,
                       TO_CHAR(v.vent_fecha, 'dd/mm/yyyy')                                       AS fecha,
                       COALESCE(TO_CHAR(v.vent_intervalo_fecha_vence, 'dd/mm/yyyy'), 'N/A')      AS vence,
                       c.cli_nombre || ' ' || c.cli_apellido                                     AS cliente,
                       COALESCE(c.cli_ruc, 'S/RUC')                                              AS ruc,
                       v.condicion_pago,
                       COALESCE(v.vent_cant_cuota::varchar, '0')                                 AS cuotas,
                       f.fun_nom || ' ' || f.fun_apellido                                        AS funcionario,
                       e.emp_razon_social                                                        AS empresa,
                       s.suc_razon_social                                                        AS sucursal,
                       v.vent_estado                                                             AS estado
                FROM ventas_cab v
                JOIN clientes   c ON c.id = v.clientes_id
                JOIN funcionario f ON f.id = v.funcionario_id
                JOIN empresa     e ON e.id = v.empresa_id
                JOIN sucursal    s ON s.id = v.sucursal_id
                WHERE v.vent_estado IN ('CONFIRMADO', 'PROCESADO')
                  AND v.vent_fecha BETWEEN :desde AND :hasta
                ORDER BY v.vent_fecha ASC
                {$pg}
            ",

            'pedido_ventas' => "
                SELECT pv.id,
                       TO_CHAR(pv.ped_ven_fecha, 'dd/mm/yyyy')                                   AS fecha,
                       COALESCE(TO_CHAR(pv.ped_ven_vence, 'dd/mm/yyyy'), 'N/A')                  AS entrega,
                       c.cli_nombre || ' ' || c.cli_apellido                                     AS cliente,
                       pv.ped_ven_observaciones                                                  AS observaciones,
                       f.fun_nom || ' ' || f.fun_apellido                                        AS funcionario,
                       e.emp_razon_social                                                        AS empresa,
                       s.suc_razon_social                                                        AS sucursal,
                       pv.ped_ven_estado                                                         AS estado
                FROM pedidos_ventas pv
                JOIN clientes    c ON c.id = pv.clientes_id
                JOIN funcionario f ON f.id = pv.funcionario_id
                JOIN empresa     e ON e.id = pv.empresa_id
                JOIN sucursal    s ON s.id = pv.sucursal_id
                WHERE pv.ped_ven_estado = 'PROCESADO'
                  AND pv.ped_ven_fecha BETWEEN :desde AND :hasta
                ORDER BY pv.ped_ven_fecha ASC
                {$pg}
            ",

            'nota_remi_vent' => "
                SELECT nrv.id,
                       TO_CHAR(nrv.nota_remi_vent_fecha, 'dd/mm/yyyy')                           AS fecha,
                       c.cli_nombre || ' ' || c.cli_apellido                                     AS cliente,
                       COALESCE(nrv.nota_remi_vent_observaciones, '')                            AS observaciones,
                       f.fun_nom || ' ' || f.fun_apellido                                        AS funcionario,
                       e.emp_razon_social                                                        AS empresa,
                       s.suc_razon_social                                                        AS sucursal,
                       nrv.nota_remi_vent_estado                                                 AS estado
                FROM nota_remi_vent nrv
                JOIN clientes    c ON c.id = nrv.clientes_id
                JOIN funcionario f ON f.id = nrv.funcionario_id
                JOIN empresa     e ON e.id = nrv.empresa_id
                JOIN sucursal    s ON s.id = nrv.sucursal_id
                WHERE nrv.nota_remi_vent_estado = 'CONFIRMADA'
                  AND nrv.nota_remi_vent_fecha BETWEEN :desde AND :hasta
                ORDER BY nrv.nota_remi_vent_fecha ASC
                {$pg}
            ",

            'notas_vent' => "
                SELECT nvc.id,
                       TO_CHAR(nvc.nota_vent_fecha, 'dd/mm/yyyy')                                AS fecha,
                       COALESCE(TO_CHAR(nvc.nota_vent_intervalo_fecha_vence, 'dd/mm/yyyy'), 'N/A') AS vence,
                       nvc.nota_vent_tipo                                                        AS tipo,
                       c.cli_nombre || ' ' || c.cli_apellido                                     AS cliente,
                       nvc.nota_vene_condicion_pago                                              AS condicion_pago,
                       COALESCE(nvc.nota_vent_cant_cuota::varchar, '0')                          AS cuotas,
                       f.fun_nom || ' ' || f.fun_apellido                                        AS funcionario,
                       e.emp_razon_social                                                        AS empresa,
                       s.suc_razon_social                                                        AS sucursal,
                       nvc.nota_vent_estado                                                      AS estado
                FROM notas_vent_cab nvc
                JOIN clientes    c ON c.id = nvc.clientes_id
                JOIN funcionario f ON f.id = nvc.funcionario_id
                JOIN empresa     e ON e.id = nvc.empresa_id
                JOIN sucursal    s ON s.id = nvc.sucursal_id
                WHERE nvc.nota_vent_estado IN ('CONFIRMADO', 'PROCESADO')
                  AND nvc.nota_vent_fecha BETWEEN :desde AND :hasta
                ORDER BY nvc.nota_vent_fecha ASC
                {$pg}
            ",

            'cobros' => "
                SELECT cc.id,
                       TO_CHAR(cc.cobro_fecha, 'dd/mm/yyyy')                                     AS fecha,
                       cli.cli_nombre || ' ' || cli.cli_apellido                                 AS cliente,
                       cc.cobro_importe                                                          AS importe,
                       fc.for_cob_descripcion                                                    AS forma_cobro,
                       f.fun_nom || ' ' || f.fun_apellido                                        AS funcionario,
                       e.emp_razon_social                                                        AS empresa,
                       s.suc_razon_social                                                        AS sucursal,
                       cc.cobro_estado                                                           AS estado
                FROM cobros_cab cc
                JOIN forma_cobro fc ON fc.id  = cc.forma_cobro_id
                JOIN clientes   cli ON cli.id = cc.clientes_id
                JOIN funcionario  f ON f.id   = cc.funcionario_id
                JOIN empresa      e ON e.id   = cc.empresa_id
                JOIN sucursal     s ON s.id   = cc.sucursal_id
                WHERE cc.cobro_estado = 'CONFIRMADO'
                  AND cc.cobro_fecha BETWEEN :desde AND :hasta
                ORDER BY cc.cobro_fecha ASC
                {$pg}
            ",

            'libro_ventas' => "
                SELECT lv.ventas_cab_id                              AS id,
                       TO_CHAR(lv.\"libV_fecha\", 'dd/mm/yyyy')     AS fecha,
                       lv.cli_nombre || ' ' || lv.cli_apellido      AS cliente,
                       lv.cli_ruc                                   AS ruc,
                       lv.condicion_pago,
                       lv.tip_imp_nom                               AS impuesto,
                       lv.\"libV_monto\"                            AS monto,
                       lv.\"libV_cuota\"                            AS cuota
                FROM libro_ventas lv
                WHERE lv.\"libV_fecha\" BETWEEN :desde AND :hasta
                ORDER BY lv.\"libV_fecha\" ASC
                {$pg}
            ",

            default => throw new \InvalidArgumentException("SQL no definido para: {$tipo}"),
        };
    }

    private function getSqlCount(string $tipo): string
    {
        return match ($tipo) {
            'ventas'         => "SELECT COUNT(*) AS total FROM ventas_cab
                                 WHERE vent_estado IN ('CONFIRMADO','PROCESADO')
                                   AND vent_fecha BETWEEN :desde AND :hasta",

            'pedido_ventas'  => "SELECT COUNT(*) AS total FROM pedidos_ventas
                                 WHERE ped_ven_estado = 'PROCESADO'
                                   AND ped_ven_fecha BETWEEN :desde AND :hasta",

            'nota_remi_vent' => "SELECT COUNT(*) AS total FROM nota_remi_vent
                                 WHERE nota_remi_vent_estado = 'CONFIRMADA'
                                   AND nota_remi_vent_fecha BETWEEN :desde AND :hasta",

            'notas_vent'     => "SELECT COUNT(*) AS total FROM notas_vent_cab
                                 WHERE nota_vent_estado IN ('CONFIRMADO','PROCESADO')
                                   AND nota_vent_fecha BETWEEN :desde AND :hasta",

            'cobros'         => "SELECT COUNT(*) AS total FROM cobros_cab
                                 WHERE cobro_estado = 'CONFIRMADO'
                                   AND cobro_fecha BETWEEN :desde AND :hasta",

            'libro_ventas'   => "SELECT COUNT(*) AS total FROM libro_ventas
                                 WHERE \"libV_fecha\" BETWEEN :desde AND :hasta",

            default => throw new \InvalidArgumentException("SQL COUNT no definido para: {$tipo}"),
        };
    }

    private function getSqlTotales(string $tipo): string
    {
        return match ($tipo) {
            'cobros'       => "SELECT SUM(cobro_importe) AS total_cobrado, COUNT(*) AS registros
                               FROM cobros_cab
                               WHERE cobro_estado = 'CONFIRMADO'
                                 AND cobro_fecha BETWEEN :desde AND :hasta",

            'libro_ventas' => "SELECT SUM(\"libV_monto\") AS total_monto, COUNT(*) AS registros
                               FROM libro_ventas
                               WHERE \"libV_fecha\" BETWEEN :desde AND :hasta",

            default        => "SELECT 0 AS dummy FROM (SELECT 1) t WHERE FALSE",
        };
    }
}
