<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class InformeServicioService
{
    public function ejecutar(string $tipo, array $params): array
    {
        $config = config("informes_servicio.{$tipo}");

        if (!$config) {
            abort(422, "Tipo de informe no válido: {$tipo}");
        }

        $desde  = $params['desde'];
        $hasta  = $params['hasta'];
        $start  = max(0,   (int)($params['start']  ?? 0));
        $length = min(500, max(1, (int)($params['length'] ?? 500)));
        $ttl    = $config['cache_ttl'] ?? 0;

        $cacheBase = "informe_servicio_{$tipo}_{$desde}_{$hasta}";

        $total = $ttl > 0
            ? Cache::remember("{$cacheBase}_total", $ttl,
                fn () => $this->contarRegistros($tipo, $desde, $hasta))
            : $this->contarRegistros($tipo, $desde, $hasta);

        $data = $ttl > 0
            ? Cache::remember("{$cacheBase}_{$start}_{$length}", $ttl,
                fn () => $this->consultarDatos($tipo, $desde, $hasta, $start, $length))
            : $this->consultarDatos($tipo, $desde, $hasta, $start, $length);

        return [
            'draw'            => (int)($params['draw'] ?? 1),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $data,
            'columnas'        => $config['columnas'],
            'titulo'          => $config['titulo'],
            'totales'         => [],
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

    // ── SQLs de datos ─────────────────────────────────────────────────────────

    private function getSqlData(string $tipo, int $length, int $start): string
    {
        $pg = "LIMIT {$length} OFFSET {$start}";

        return match ($tipo) {

            'recepcion' => "
                SELECT rc.id,
                       TO_CHAR(rc.recep_cab_fecha, 'dd/mm/yyyy')            AS fecha,
                       c.cli_nombre || ' ' || COALESCE(c.cli_apellido, '')  AS cliente,
                       COALESCE(ts.tipo_serv_nombre, 'N/A')                 AS tipo_servicio,
                       m.marc_nom || ' ' || mo.modelo_nom                   AS vehiculo,
                       rc.recep_cab_prioridad                               AS prioridad,
                       rc.recep_cab_kilometraje                             AS kilometraje,
                       f.fun_nom || ' ' || f.fun_apellido                   AS funcionario,
                       e.emp_razon_social                                   AS empresa,
                       s.suc_razon_social                                   AS sucursal,
                       rc.recep_cab_estado                                  AS estado
                FROM recep_cab rc
                JOIN funcionario   f  ON f.id  = rc.funcionario_id
                JOIN sucursal      s  ON s.id  = rc.sucursal_id
                JOIN empresa       e  ON e.id  = rc.empresa_id
                JOIN clientes      c  ON c.id  = rc.clientes_id
                JOIN tipo_vehiculo tv ON tv.id = rc.tipo_vehiculo_id
                JOIN marca         m  ON m.id  = tv.marca_id
                JOIN modelo        mo ON mo.id = tv.modelo_id
                LEFT JOIN tipo_servicio ts ON ts.id = rc.tipo_servicio_id
                WHERE rc.recep_cab_estado IN ('CONFIRMADO', 'PROCESADO')
                  AND rc.recep_cab_fecha BETWEEN :desde AND :hasta
                ORDER BY rc.recep_cab_fecha ASC
                {$pg}
            ",

            'diagnostico' => "
                SELECT dc.id,
                       TO_CHAR(dc.diag_cab_fecha, 'dd/mm/yyyy')             AS fecha,
                       c.cli_nombre || ' ' || COALESCE(c.cli_apellido, '')  AS cliente,
                       COALESCE(td.tipo_diag_nombre, 'N/A')                 AS tipo_diagnostico,
                       COALESCE(ts.tipo_serv_nombre, 'N/A')                 AS tipo_servicio,
                       COALESCE(m.marc_nom || ' ' || mo.modelo_nom, 'N/A')  AS vehiculo,
                       dc.diag_cab_prioridad                                AS prioridad,
                       f.fun_nom || ' ' || f.fun_apellido                   AS funcionario,
                       e.emp_razon_social                                   AS empresa,
                       s.suc_razon_social                                   AS sucursal,
                       dc.diag_cab_estado                                   AS estado
                FROM diagnostico_cab dc
                JOIN funcionario f        ON f.id  = dc.funcionario_id
                JOIN sucursal    s        ON s.id  = dc.sucursal_id
                JOIN empresa     e        ON e.id  = dc.empresa_id
                JOIN clientes    c        ON c.id  = dc.clientes_id
                LEFT JOIN tipo_diagnostico td ON td.id = dc.tipo_diagnostico_id
                LEFT JOIN recep_cab        rc ON rc.id = dc.recep_cab_id
                LEFT JOIN tipo_servicio    ts ON ts.id = rc.tipo_servicio_id
                LEFT JOIN tipo_vehiculo    tv ON tv.id = rc.tipo_vehiculo_id
                LEFT JOIN marca            m  ON m.id  = tv.marca_id
                LEFT JOIN modelo           mo ON mo.id = tv.modelo_id
                WHERE dc.diag_cab_estado IN ('CONFIRMADO', 'PROCESADO')
                  AND dc.diag_cab_fecha BETWEEN :desde AND :hasta
                ORDER BY dc.diag_cab_fecha ASC
                {$pg}
            ",

            'presupuesto_serv' => "
                SELECT psc.id,
                       TO_CHAR(psc.pres_serv_cab_fecha, 'dd/mm/yyyy')                              AS fecha,
                       COALESCE(TO_CHAR(psc.pres_serv_cab_fecha_vence, 'dd/mm/yyyy'), 'N/A')       AS vence,
                       c.cli_nombre || ' ' || COALESCE(c.cli_apellido, '')                         AS cliente,
                       ts.tipo_serv_nombre                                                         AS tipo_servicio,
                       m.marc_nom || ' ' || mo.modelo_nom                                          AS vehiculo,
                       f.fun_nom || ' ' || f.fun_apellido                                          AS funcionario,
                       e.emp_razon_social                                                          AS empresa,
                       s.suc_razon_social                                                          AS sucursal,
                       psc.pres_serv_cab_estado                                                    AS estado
                FROM presupuesto_serv_cab psc
                JOIN funcionario   f  ON f.id  = psc.funcionario_id
                JOIN empresa       e  ON e.id  = psc.empresa_id
                JOIN sucursal      s  ON s.id  = psc.sucursal_id
                JOIN clientes      c  ON c.id  = psc.clientes_id
                JOIN tipo_servicio ts ON ts.id = psc.tipo_servicio_id
                JOIN tipo_vehiculo tv ON tv.id = psc.tipo_vehiculo_id
                JOIN marca         m  ON m.id  = tv.marca_id
                JOIN modelo        mo ON mo.id = tv.modelo_id
                WHERE psc.pres_serv_cab_estado = 'PROCESADO'
                  AND psc.pres_serv_cab_fecha BETWEEN :desde AND :hasta
                ORDER BY psc.pres_serv_cab_fecha ASC
                {$pg}
            ",

            'orden_servicio' => "
                SELECT osc.id,
                       TO_CHAR(osc.ord_serv_fecha, 'dd/mm/yyyy')                             AS fecha,
                       COALESCE(TO_CHAR(osc.ord_serv_fecha_vence, 'dd/mm/yyyy'), 'N/A')      AS vence,
                       c.cli_nombre || ' ' || COALESCE(c.cli_apellido, '')                   AS cliente,
                       td.tipo_diag_nombre                                                   AS tipo_diagnostico,
                       m.marc_nom || ' ' || mo.modelo_nom                                    AS vehiculo,
                       osc.ord_serv_tipo                                                     AS tipo,
                       f.fun_nom || ' ' || f.fun_apellido                                    AS funcionario,
                       e.emp_razon_social                                                    AS empresa,
                       s.suc_razon_social                                                    AS sucursal,
                       osc.ord_serv_estado                                                   AS estado
                FROM orden_serv_cab osc
                JOIN funcionario      f  ON f.id  = osc.funcionario_id
                JOIN empresa          e  ON e.id  = osc.empresa_id
                JOIN sucursal         s  ON s.id  = osc.sucursal_id
                JOIN clientes         c  ON c.id  = osc.clientes_id
                JOIN tipo_diagnostico td ON td.id = osc.tipo_diagnostico_id
                JOIN tipo_vehiculo    tv ON tv.id = osc.tipo_vehiculo_id
                JOIN marca            m  ON m.id  = tv.marca_id
                JOIN modelo           mo ON mo.id = tv.modelo_id
                WHERE osc.ord_serv_estado = 'PROCESADO'
                  AND osc.ord_serv_fecha BETWEEN :desde AND :hasta
                ORDER BY osc.ord_serv_fecha ASC
                {$pg}
            ",

            'contrato' => "
                SELECT csc.id,
                       TO_CHAR(csc.contrato_fecha, 'dd/mm/yyyy')                             AS fecha,
                       cli.cli_nombre || ' ' || COALESCE(cli.cli_apellido, '')               AS cliente,
                       ts.tipo_serv_nombre                                                   AS tipo_servicio,
                       tc.tipo_cont_nombre                                                   AS tipo_contrato,
                       csc.contrato_condicion_pago                                           AS condicion_pago,
                       COALESCE(csc.contrato_cuotas::varchar, '0')                           AS cuotas,
                       f.fun_nom || ' ' || f.fun_apellido                                    AS funcionario,
                       e.emp_razon_social                                                    AS empresa,
                       s.suc_razon_social                                                    AS sucursal,
                       csc.contrato_estado                                                   AS estado
                FROM contrato_serv_cab csc
                JOIN funcionario   f   ON f.id   = csc.funcionario_id
                JOIN empresa       e   ON e.id   = csc.empresa_id
                JOIN sucursal      s   ON s.id   = csc.sucursal_id
                JOIN clientes      cli ON cli.id = csc.clientes_id
                JOIN tipo_servicio ts  ON ts.id  = csc.tipo_servicio_id
                JOIN tipo_contrato tc  ON tc.id  = csc.tipo_contrato_id
                WHERE csc.contrato_estado = 'CONFIRMADO'
                  AND csc.contrato_fecha BETWEEN :desde AND :hasta
                ORDER BY csc.contrato_fecha ASC
                {$pg}
            ",

            'reclamo' => "
                SELECT rcc.id,
                       TO_CHAR(rcc.rec_cli_cab_fecha, 'dd/mm/yyyy')                          AS fecha,
                       c.cli_nombre || ' ' || COALESCE(c.cli_apellido, '')                   AS cliente,
                       rcc.rec_cli_cab_prioridad                                             AS prioridad,
                       rcc.rec_cli_cab_observacion                                           AS observacion,
                       f.fun_nom || ' ' || f.fun_apellido                                    AS funcionario,
                       e.emp_razon_social                                                    AS empresa,
                       s.suc_razon_social                                                    AS sucursal,
                       rcc.rec_cli_cab_estado                                                AS estado
                FROM reclamo_cli_cab rcc
                JOIN sucursal    s ON s.id = rcc.sucursal_id
                JOIN empresa     e ON e.id = rcc.empresa_id
                JOIN clientes    c ON c.id = rcc.clientes_id
                JOIN funcionario f ON f.id = rcc.funcionario_id
                WHERE rcc.rec_cli_cab_estado IN ('PENDIENTE', 'EN PROCESO', 'RESUELTO')
                  AND rcc.rec_cli_cab_fecha BETWEEN :desde AND :hasta
                ORDER BY rcc.rec_cli_cab_fecha ASC
                {$pg}
            ",

            'promociones' => "
                SELECT pc.id,
                       pc.prom_cab_nombre                                                    AS nombre,
                       tp.tipo_prom_nombre                                                   AS tipo_promocion,
                       TO_CHAR(pc.prom_cab_fecha_inicio, 'dd/mm/yyyy')                       AS fecha_inicio,
                       TO_CHAR(pc.prom_cab_fecha_fin,    'dd/mm/yyyy')                       AS fecha_fin,
                       pc.prom_cab_observaciones                                             AS observaciones,
                       f.fun_nom || ' ' || f.fun_apellido                                    AS funcionario,
                       e.emp_razon_social                                                    AS empresa,
                       s.suc_razon_social                                                    AS sucursal,
                       pc.prom_cab_estado                                                    AS estado
                FROM promociones_cab pc
                JOIN sucursal         s  ON s.id  = pc.sucursal_id
                JOIN empresa          e  ON e.id  = pc.empresa_id
                JOIN funcionario      f  ON f.id  = pc.funcionario_id
                JOIN tipo_promociones tp ON tp.id = pc.tipo_promociones_id
                WHERE pc.prom_cab_estado = 'CONFIRMADO'
                  AND pc.prom_cab_fecha_registro BETWEEN :desde AND :hasta
                ORDER BY pc.prom_cab_fecha_registro ASC
                {$pg}
            ",

            'descuentos' => "
                SELECT dc.id,
                       dc.desc_cab_nombre                                                    AS nombre,
                       td.tipo_desc_nombre                                                   AS tipo_descuento,
                       dc.desc_cab_porcentaje                                                AS porcentaje,
                       TO_CHAR(dc.desc_cab_fecha_inicio, 'dd/mm/yyyy')                       AS fecha_inicio,
                       TO_CHAR(dc.desc_cab_fecha_fin,    'dd/mm/yyyy')                       AS fecha_fin,
                       dc.desc_cab_observaciones                                             AS observaciones,
                       f.fun_nom || ' ' || f.fun_apellido                                    AS funcionario,
                       e.emp_razon_social                                                    AS empresa,
                       s.suc_razon_social                                                    AS sucursal,
                       dc.desc_cab_estado                                                    AS estado
                FROM descuentos_cab dc
                JOIN sucursal        s  ON s.id  = dc.sucursal_id
                JOIN empresa         e  ON e.id  = dc.empresa_id
                JOIN funcionario     f  ON f.id  = dc.funcionario_id
                JOIN tipo_descuentos td ON td.id = dc.tipo_descuentos_id
                WHERE dc.desc_cab_estado = 'CONFIRMADO'
                  AND dc.desc_cab_fecha_registro BETWEEN :desde AND :hasta
                ORDER BY dc.desc_cab_fecha_registro ASC
                {$pg}
            ",

            default => throw new \InvalidArgumentException("SQL no definido para: {$tipo}"),
        };
    }

    // ── SQLs de conteo ────────────────────────────────────────────────────────

    private function getSqlCount(string $tipo): string
    {
        return match ($tipo) {
            'recepcion'        => "SELECT COUNT(*) AS total FROM recep_cab
                                   WHERE recep_cab_estado IN ('CONFIRMADO', 'PROCESADO')
                                     AND recep_cab_fecha BETWEEN :desde AND :hasta",

            'diagnostico'      => "SELECT COUNT(*) AS total FROM diagnostico_cab
                                   WHERE diag_cab_estado IN ('CONFIRMADO', 'PROCESADO')
                                     AND diag_cab_fecha BETWEEN :desde AND :hasta",

            'presupuesto_serv' => "SELECT COUNT(*) AS total FROM presupuesto_serv_cab
                                   WHERE pres_serv_cab_estado = 'PROCESADO'
                                     AND pres_serv_cab_fecha BETWEEN :desde AND :hasta",

            'orden_servicio'   => "SELECT COUNT(*) AS total FROM orden_serv_cab
                                   WHERE ord_serv_estado = 'PROCESADO'
                                     AND ord_serv_fecha BETWEEN :desde AND :hasta",

            'contrato'         => "SELECT COUNT(*) AS total FROM contrato_serv_cab
                                   WHERE contrato_estado = 'CONFIRMADO'
                                     AND contrato_fecha BETWEEN :desde AND :hasta",

            'reclamo'          => "SELECT COUNT(*) AS total FROM reclamo_cli_cab
                                   WHERE rec_cli_cab_estado IN ('PENDIENTE', 'EN PROCESO', 'RESUELTO')
                                     AND rec_cli_cab_fecha BETWEEN :desde AND :hasta",

            'promociones'      => "SELECT COUNT(*) AS total FROM promociones_cab
                                   WHERE prom_cab_estado = 'CONFIRMADO'
                                     AND prom_cab_fecha_registro BETWEEN :desde AND :hasta",

            'descuentos'       => "SELECT COUNT(*) AS total FROM descuentos_cab
                                   WHERE desc_cab_estado = 'CONFIRMADO'
                                     AND desc_cab_fecha_registro BETWEEN :desde AND :hasta",

            default => throw new \InvalidArgumentException("SQL COUNT no definido para: {$tipo}"),
        };
    }
}
