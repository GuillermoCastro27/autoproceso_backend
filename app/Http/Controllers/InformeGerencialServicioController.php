<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformeGerencialServicioController extends Controller
{
    private function validarFechas(Request $r): void
    {
        $r->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);
    }

    // ── Helpers genéricos ─────────────────────────────────────────────────────

    private function graficoEstados(string $tabla, string $colFecha, string $colEstado, string $titulo, string $color, string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT {$colEstado} AS estado, COUNT(*) AS cantidad
            FROM {$tabla}
            WHERE {$colFecha} BETWEEN :desde AND :hasta
            GROUP BY {$colEstado}
            ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'estado_' . $tabla,
            'titulo'       => $titulo,
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->estado ?? 'Sin estado', $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => $color]],
            'columnas'     => [['key' => 'estado', 'label' => 'Estado'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    // ── Estadísticas por tipo ─────────────────────────────────────────────────

    public function estadisticas(Request $r)
    {
        $this->validarFechas($r);
        $tipo  = $r->input('tipo', '');
        $desde = $r->desde;
        $hasta = $r->hasta;

        $secciones = match ($tipo) {
            'insumos'            => $this->statsInsumos($desde, $hasta),
            'solicitud_servicio' => $this->statsSolicitudServicio($desde, $hasta),
            'recepcion'          => $this->statsRecepcion($desde, $hasta),
            'diagnostico'        => $this->statsDiagnostico($desde, $hasta),
            'presupuesto_serv'   => $this->statsPresupuestoServ($desde, $hasta),
            'orden_servicio'     => $this->statsOrdenServicio($desde, $hasta),
            'contrato'           => $this->statsContrato($desde, $hasta),
            'reclamo'            => $this->statsReclamo($desde, $hasta),
            'promociones'        => $this->statsPromociones($desde, $hasta),
            'descuentos'         => $this->statsDescuentos($desde, $hasta),
            default              => [],
        };

        $tieneFiltros = collect($secciones)->contains(fn($s) => !empty($s['filtros']));
        $catalogos    = [];
        if ($tieneFiltros) {
            $catalogos = [
                'sucursales' => DB::select("SELECT id, suc_razon_social AS nombre FROM sucursal ORDER BY suc_razon_social"),
            ];
        }

        return response()->json(['secciones' => $secciones, 'catalogos' => $catalogos]);
    }

    public function seccion(Request $r)
    {
        $this->validarFechas($r);
        $tipo    = $r->input('tipo', '');
        $seccion = $r->input('seccion', '');
        $desde   = $r->desde;
        $hasta   = $r->hasta;
        $sucId   = $r->filled('sucursal_id') ? (int)$r->sucursal_id : null;

        $result = match ($tipo . '::' . $seccion) {
            'insumos::ins_mes'                    => $this->insPorMes($desde, $hasta, $sucId),
            'insumos::ins_items'                  => $this->insTopItems($desde, $hasta, $sucId),
            'insumos::ins_equipo'                 => $this->insPorEquipo($desde, $hasta, $sucId),
            'solicitud_servicio::sol_mes'         => $this->solPorMes($desde, $hasta, $sucId),
            'solicitud_servicio::sol_top_clientes'=> $this->solTopClientes($desde, $hasta, $sucId),
            'recepcion::recep_mes'            => $this->recepPorMes($desde, $hasta, $sucId),
            'recepcion::recep_marca'          => $this->recepTopMarcas($desde, $hasta, $sucId),
            'diagnostico::diag_mes'           => $this->diagPorMes($desde, $hasta, $sucId),
            'diagnostico::diag_tipo_serv'     => $this->diagPorTipoServ($desde, $hasta, $sucId),
            'presupuesto_serv::psv_mes'       => $this->psvPorMes($desde, $hasta, $sucId),
            'presupuesto_serv::psv_items'     => $this->psvTopItems($desde, $hasta, $sucId),
            'orden_servicio::osv_mes'         => $this->osvPorMes($desde, $hasta, $sucId),
            'orden_servicio::osv_equipo'      => $this->osvPorEquipo($desde, $hasta, $sucId),
            'contrato::contr_mes'             => $this->contrPorMes($desde, $hasta, $sucId),
            'contrato::contr_tipo_serv'       => $this->contrPorTipoServ($desde, $hasta, $sucId),
            'reclamo::rcl_mes'                => $this->rclPorMes($desde, $hasta, $sucId),
            'reclamo::rcl_top_clientes'       => $this->rclTopClientes($desde, $hasta, $sucId),
            'promociones::prom_mes'           => $this->promPorMes($desde, $hasta, $sucId),
            'promociones::prom_items'         => $this->promTopItems($desde, $hasta, $sucId),
            'descuentos::desc_mes'            => $this->descPorMes($desde, $hasta, $sucId),
            default                           => null,
        };

        if (!$result) {
            return response()->json(['error' => 'Sección no encontrada'], 404);
        }

        return response()->json(['seccion' => $result]);
    }

    // ── INSUMOS UTILIZADOS ────────────────────────────────────────────────────

    private function statsInsumos(string $desde, string $hasta): array
    {
        return [
            $this->graficoEstados('insumos_cab', 'ins_cab_fecha_registro', 'ins_cab_estado', 'Insumos por Estado', '#2980b9', $desde, $hasta),
            $this->insPorMes($desde, $hasta),
            $this->insTopItems($desde, $hasta),
            $this->insPorEquipo($desde, $hasta),
            $this->insTopMarcas($desde, $hasta),
        ];
    }

    private function insPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE ic.ins_cab_fecha_registro BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND osc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', ic.ins_cab_fecha_registro), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM insumos_cab ic
            JOIN orden_serv_cab osc ON osc.id = ic.orden_serv_cab_id
            {$where}
            GROUP BY DATE_TRUNC('month', ic.ins_cab_fecha_registro)
            ORDER BY DATE_TRUNC('month', ic.ins_cab_fecha_registro)
        ", $params);

        return [
            'id'           => 'ins_mes',
            'titulo'       => 'Insumos Registrados por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Registros', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#27ae60']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function insTopItems(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE ic.ins_cab_fecha_registro BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND osc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   SUM(d.ins_det_cantidad) AS total_cantidad,
                   COALESCE(SUM(d.ins_det_cantidad * d.ins_det_costo), 0) AS total_monto
            FROM insumos_det d
            JOIN insumos_cab        ic  ON ic.id  = d.insumos_cab_id
            JOIN orden_serv_cab     osc ON osc.id = ic.orden_serv_cab_id
            JOIN items              i   ON i.id   = d.item_id
            {$where}
            GROUP BY i.item_decripcion
            ORDER BY total_cantidad DESC LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'ins_items',
            'titulo'       => 'Top ' . $topN . ' Ítems Más Utilizados',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->item, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (float)$r->total_cantidad, $data), 'color' => '#e74c3c']],
            'columnas'     => [
                ['key' => 'item',           'label' => 'Ítem'],
                ['key' => 'total_cantidad', 'label' => 'Cantidad'],
                ['key' => 'total_monto',    'label' => 'Monto (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function insPorEquipo(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE ic.ins_cab_fecha_registro BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND osc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT COALESCE(et.equipo_nombre, 'Sin equipo') AS equipo,
                   COUNT(*) AS cantidad
            FROM insumos_cab ic
            JOIN orden_serv_cab osc ON osc.id = ic.orden_serv_cab_id
            LEFT JOIN equipo_trabajo et ON et.id = osc.equipo_trabajo_id
            {$where}
            GROUP BY et.equipo_nombre ORDER BY cantidad DESC
        ", $params);

        return [
            'id'           => 'ins_equipo',
            'titulo'       => 'Insumos por Equipo de Trabajo',
            'tipo_grafico' => 'bar',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->equipo, $data),
            'datasets'     => [['label' => 'Registros', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#8e44ad']],
            'columnas'     => [['key' => 'equipo', 'label' => 'Equipo de Trabajo'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function insTopMarcas(string $desde, string $hasta, int $topN = 10): array
    {
        $topN = min(50, max(1, $topN));
        $data = DB::select("
            SELECT COALESCE(m.marc_nom, 'Sin marca') AS marca,
                   SUM(d.ins_det_cantidad) AS total_cantidad
            FROM insumos_det d
            JOIN insumos_cab    ic ON ic.id = d.insumos_cab_id
            LEFT JOIN marca      m ON m.id  = d.marca_id
            WHERE ic.ins_cab_fecha_registro BETWEEN :desde AND :hasta
              AND d.marca_id IS NOT NULL
            GROUP BY m.marc_nom
            ORDER BY total_cantidad DESC LIMIT {$topN}
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'ins_marcas',
            'titulo'       => 'Top ' . $topN . ' Marcas en Insumos',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->marca, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (float)$r->total_cantidad, $data)]],
            'columnas'     => [['key' => 'marca', 'label' => 'Marca'], ['key' => 'total_cantidad', 'label' => 'Cantidad Total']],
            'tabla'        => $data,
        ];
    }

    // ── SOLICITUD DE SERVICIO ─────────────────────────────────────────────────

    private function statsSolicitudServicio(string $desde, string $hasta): array
    {
        return [
            $this->solPorTipoServ($desde, $hasta),
            $this->solPorPrioridad($desde, $hasta),
            $this->graficoEstados('solicitudes_cab', 'soli_cab_fecha', 'soli_cab_estado', 'Solicitudes por Estado', '#2980b9', $desde, $hasta),
            $this->solPorMes($desde, $hasta),
            $this->solTopClientes($desde, $hasta),
        ];
    }

    private function solPorTipoServ(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(ts.tipo_serv_nombre, 'Sin tipo') AS tipo_servicio,
                   COUNT(*) AS cantidad
            FROM solicitudes_cab sc
            LEFT JOIN tipo_servicio ts ON ts.id = sc.tipo_servicio_id
            WHERE sc.soli_cab_fecha BETWEEN :desde AND :hasta
            GROUP BY ts.tipo_serv_nombre ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'sol_tipo_serv',
            'titulo'       => 'Solicitudes por Tipo de Servicio',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->tipo_servicio, $data),
            'datasets'     => [['label' => 'Solicitudes', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#27ae60']],
            'columnas'     => [['key' => 'tipo_servicio', 'label' => 'Tipo de Servicio'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function solPorPrioridad(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(soli_cab_prioridad, 'Sin prioridad') AS prioridad,
                   COUNT(*) AS cantidad
            FROM solicitudes_cab
            WHERE soli_cab_fecha BETWEEN :desde AND :hasta
            GROUP BY soli_cab_prioridad ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'sol_prioridad',
            'titulo'       => 'Solicitudes por Prioridad',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->prioridad, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [['key' => 'prioridad', 'label' => 'Prioridad'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function solPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE soli_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', soli_cab_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM solicitudes_cab
            {$where}
            GROUP BY DATE_TRUNC('month', soli_cab_fecha)
            ORDER BY DATE_TRUNC('month', soli_cab_fecha)
        ", $params);

        return [
            'id'           => 'sol_mes',
            'titulo'       => 'Solicitudes por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Solicitudes', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#8e44ad']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function solTopClientes(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $where  = "WHERE sc.soli_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT COALESCE(c.cli_nombre || ' ' || c.cli_apellido, 'Sin cliente') AS cliente,
                   COUNT(*) AS cantidad
            FROM solicitudes_cab sc
            JOIN clientes c ON c.id = sc.clientes_id
            {$where}
            GROUP BY c.cli_nombre, c.cli_apellido
            ORDER BY cantidad DESC LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'sol_top_clientes',
            'titulo'       => 'Top ' . $topN . ' Clientes con Más Solicitudes',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->cliente, $data),
            'datasets'     => [['label' => 'Solicitudes', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#2980b9']],
            'columnas'     => [['key' => 'cliente', 'label' => 'Cliente'], ['key' => 'cantidad', 'label' => 'Solicitudes']],
            'tabla'        => $data,
        ];
    }

    // ── RECEPCIÓN ─────────────────────────────────────────────────────────────

    private function statsRecepcion(string $desde, string $hasta): array
    {
        return [
            $this->recepPorTipoServ($desde, $hasta),
            $this->graficoEstados('recep_cab', 'recep_cab_fecha', 'recep_cab_estado', 'Recepciones por Estado', '#2980b9', $desde, $hasta),
            $this->recepPorMes($desde, $hasta),
            $this->recepPorPrioridad($desde, $hasta),
            $this->recepTopMarcas($desde, $hasta),
        ];
    }

    private function recepPorTipoServ(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(ts.tipo_serv_nombre, 'Sin tipo') AS tipo_servicio,
                   COUNT(*) AS cantidad
            FROM recep_cab rc
            LEFT JOIN tipo_servicio ts ON ts.id = rc.tipo_servicio_id
            WHERE rc.recep_cab_fecha BETWEEN :desde AND :hasta
            GROUP BY ts.tipo_serv_nombre ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'recep_tipo_serv',
            'titulo'       => 'Recepciones por Tipo de Servicio',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->tipo_servicio, $data),
            'datasets'     => [['label' => 'Recepciones', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#27ae60']],
            'columnas'     => [['key' => 'tipo_servicio', 'label' => 'Tipo de Servicio'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function recepPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE rc.recep_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND rc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', rc.recep_cab_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM recep_cab rc
            {$where}
            GROUP BY DATE_TRUNC('month', rc.recep_cab_fecha)
            ORDER BY DATE_TRUNC('month', rc.recep_cab_fecha)
        ", $params);

        return [
            'id'           => 'recep_mes',
            'titulo'       => 'Recepciones por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Recepciones', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#8e44ad']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function recepPorPrioridad(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(recep_cab_prioridad, 'Sin prioridad') AS prioridad,
                   COUNT(*) AS cantidad
            FROM recep_cab
            WHERE recep_cab_fecha BETWEEN :desde AND :hasta
            GROUP BY recep_cab_prioridad ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'recep_prioridad',
            'titulo'       => 'Recepciones por Prioridad',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->prioridad, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [['key' => 'prioridad', 'label' => 'Prioridad'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function recepTopMarcas(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $where  = "WHERE rc.recep_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND rc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT COALESCE(m.marc_nom, 'Sin marca') AS marca,
                   COUNT(*) AS cantidad
            FROM recep_cab rc
            LEFT JOIN tipo_vehiculo tv ON tv.id = rc.tipo_vehiculo_id
            LEFT JOIN marca          m  ON m.id  = tv.marca_id
            {$where}
            GROUP BY m.marc_nom ORDER BY cantidad DESC LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'recep_marca',
            'titulo'       => 'Top ' . $topN . ' Marcas de Vehículo en Recepciones',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->marca, $data),
            'datasets'     => [['label' => 'Recepciones', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#e74c3c']],
            'columnas'     => [['key' => 'marca', 'label' => 'Marca'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    // ── DIAGNÓSTICO ───────────────────────────────────────────────────────────

    private function statsDiagnostico(string $desde, string $hasta): array
    {
        return [
            $this->diagPorTipo($desde, $hasta),
            $this->graficoEstados('diagnostico_cab', 'diag_cab_fecha', 'diag_cab_estado', 'Diagnósticos por Estado', '#2980b9', $desde, $hasta),
            $this->diagPorPrioridad($desde, $hasta),
            $this->diagPorMes($desde, $hasta),
            $this->diagPorTipoServ($desde, $hasta),
        ];
    }

    private function diagPorTipo(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(td.tipo_diag_nombre, 'Sin tipo') AS tipo_diagnostico,
                   COUNT(*) AS cantidad
            FROM diagnostico_cab dc
            LEFT JOIN tipo_diagnostico td ON td.id = dc.tipo_diagnostico_id
            WHERE dc.diag_cab_fecha BETWEEN :desde AND :hasta
            GROUP BY td.tipo_diag_nombre ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'diag_tipo',
            'titulo'       => 'Diagnósticos por Tipo',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->tipo_diagnostico, $data),
            'datasets'     => [['label' => 'Diagnósticos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#f39c12']],
            'columnas'     => [['key' => 'tipo_diagnostico', 'label' => 'Tipo'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function diagPorPrioridad(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(diag_cab_prioridad, 'Sin prioridad') AS prioridad,
                   COUNT(*) AS cantidad
            FROM diagnostico_cab
            WHERE diag_cab_fecha BETWEEN :desde AND :hasta
            GROUP BY diag_cab_prioridad ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'diag_prioridad',
            'titulo'       => 'Diagnósticos por Prioridad',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->prioridad, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [['key' => 'prioridad', 'label' => 'Prioridad'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function diagPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE diag_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', diag_cab_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM diagnostico_cab
            {$where}
            GROUP BY DATE_TRUNC('month', diag_cab_fecha)
            ORDER BY DATE_TRUNC('month', diag_cab_fecha)
        ", $params);

        return [
            'id'           => 'diag_mes',
            'titulo'       => 'Diagnósticos por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Diagnósticos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#16a085']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function diagPorTipoServ(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE dc.diag_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND dc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT COALESCE(ts.tipo_serv_nombre, 'Sin tipo') AS tipo_servicio,
                   COUNT(*) AS cantidad
            FROM diagnostico_cab dc
            LEFT JOIN tipo_servicio ts ON ts.id = dc.tipo_servicio_id
            {$where}
            GROUP BY ts.tipo_serv_nombre ORDER BY cantidad DESC
        ", $params);

        return [
            'id'           => 'diag_tipo_serv',
            'titulo'       => 'Diagnósticos por Tipo de Servicio',
            'tipo_grafico' => 'bar',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->tipo_servicio, $data),
            'datasets'     => [['label' => 'Diagnósticos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#2980b9']],
            'columnas'     => [['key' => 'tipo_servicio', 'label' => 'Tipo de Servicio'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    // ── PRESUPUESTO SERVICIO ──────────────────────────────────────────────────

    private function statsPresupuestoServ(string $desde, string $hasta): array
    {
        return [
            $this->graficoEstados('presupuesto_serv_cab', 'pres_serv_cab_fecha', 'pres_serv_cab_estado', 'Presupuestos de Servicio por Estado', '#2980b9', $desde, $hasta),
            $this->psvPorTipoServ($desde, $hasta),
            $this->psvPorMes($desde, $hasta),
            $this->psvTopItems($desde, $hasta),
        ];
    }

    private function psvPorTipoServ(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(ts.tipo_serv_nombre, 'Sin tipo') AS tipo_servicio,
                   COUNT(*) AS cantidad
            FROM presupuesto_serv_cab psc
            LEFT JOIN tipo_servicio ts ON ts.id = psc.tipo_servicio_id
            WHERE psc.pres_serv_cab_fecha BETWEEN :desde AND :hasta
            GROUP BY ts.tipo_serv_nombre ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'psv_tipo_serv',
            'titulo'       => 'Presupuestos de Servicio por Tipo',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->tipo_servicio, $data),
            'datasets'     => [['label' => 'Presupuestos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#27ae60']],
            'columnas'     => [['key' => 'tipo_servicio', 'label' => 'Tipo de Servicio'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function psvPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE pres_serv_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', pres_serv_cab_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM presupuesto_serv_cab
            {$where}
            GROUP BY DATE_TRUNC('month', pres_serv_cab_fecha)
            ORDER BY DATE_TRUNC('month', pres_serv_cab_fecha)
        ", $params);

        return [
            'id'           => 'psv_mes',
            'titulo'       => 'Presupuestos de Servicio por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Presupuestos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#8e44ad']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function psvTopItems(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE psc.pres_serv_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND psc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   SUM(psd.pres_serv_det_cantidad) AS total_cantidad,
                   COALESCE(SUM(psd.pres_serv_det_cantidad * psd.pres_serv_det_costo), 0) AS total_monto
            FROM presupuesto_serv_det psd
            JOIN presupuesto_serv_cab psc ON psc.id = psd.presupuesto_serv_cab_id
            JOIN items                i   ON i.id   = psd.item_id
            {$where}
            GROUP BY i.item_decripcion
            ORDER BY total_cantidad DESC LIMIT {$topN}
        ", $params);

        $pivot = [];
        foreach ($data as $row) { $pivot[$row->item] = ($pivot[$row->item] ?? 0) + (float)$row->total_cantidad; }
        arsort($pivot); $pivot = array_slice($pivot, 0, $topN, true);

        return [
            'id'           => 'psv_items',
            'titulo'       => 'Top ' . $topN . ' Ítems en Presupuestos de Servicio',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_keys($pivot),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_values($pivot), 'color' => '#e74c3c']],
            'columnas'     => [
                ['key' => 'item',           'label' => 'Ítem'],
                ['key' => 'total_cantidad', 'label' => 'Cantidad'],
                ['key' => 'total_monto',    'label' => 'Monto (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    // ── ORDEN DE SERVICIO ─────────────────────────────────────────────────────

    private function statsOrdenServicio(string $desde, string $hasta): array
    {
        return [
            $this->osvPorTipo($desde, $hasta),
            $this->graficoEstados('orden_serv_cab', 'ord_serv_fecha', 'ord_serv_estado', 'Órdenes de Servicio por Estado', '#2980b9', $desde, $hasta),
            $this->osvPorMes($desde, $hasta),
            $this->osvPorEquipo($desde, $hasta),
            $this->osvTopItems($desde, $hasta),
        ];
    }

    private function osvPorTipo(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(ord_serv_tipo, 'Sin tipo') AS tipo,
                   COUNT(*) AS cantidad
            FROM orden_serv_cab
            WHERE ord_serv_fecha BETWEEN :desde AND :hasta
            GROUP BY ord_serv_tipo ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'osv_tipo',
            'titulo'       => 'Órdenes de Servicio por Tipo',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->tipo, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [['key' => 'tipo', 'label' => 'Tipo'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function osvPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE ord_serv_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', ord_serv_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM orden_serv_cab
            {$where}
            GROUP BY DATE_TRUNC('month', ord_serv_fecha)
            ORDER BY DATE_TRUNC('month', ord_serv_fecha)
        ", $params);

        return [
            'id'           => 'osv_mes',
            'titulo'       => 'Órdenes de Servicio por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Órdenes', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#d35400']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function osvPorEquipo(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE osc.ord_serv_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND osc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT COALESCE(et.equipo_nombre, 'Sin equipo') AS equipo,
                   COUNT(*) AS cantidad
            FROM orden_serv_cab osc
            LEFT JOIN equipo_trabajo et ON et.id = osc.equipo_trabajo_id
            {$where}
            GROUP BY et.equipo_nombre ORDER BY cantidad DESC
        ", $params);

        return [
            'id'           => 'osv_equipo',
            'titulo'       => 'Órdenes de Servicio por Equipo de Trabajo',
            'tipo_grafico' => 'bar',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->equipo, $data),
            'datasets'     => [['label' => 'Órdenes', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#16a085']],
            'columnas'     => [['key' => 'equipo', 'label' => 'Equipo de Trabajo'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function osvTopItems(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE osc.ord_serv_fecha BETWEEN :desde AND :hasta AND osc.ord_serv_estado = 'CONFIRMADO'";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND osc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   SUM(osd.orden_serv_det_cantidad) AS total_cantidad
            FROM orden_serv_det osd
            JOIN orden_serv_cab osc ON osc.id = osd.orden_serv_cab_id
            JOIN items          i   ON i.id   = osd.item_id
            {$where}
            GROUP BY i.item_decripcion
            ORDER BY total_cantidad DESC LIMIT {$topN}
        ", $params);

        $pivot = [];
        foreach ($data as $row) { $pivot[$row->item] = ($pivot[$row->item] ?? 0) + (float)$row->total_cantidad; }
        arsort($pivot); $pivot = array_slice($pivot, 0, $topN, true);

        return [
            'id'           => 'osv_items',
            'titulo'       => 'Top ' . $topN . ' Ítems en Órdenes de Servicio',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'labels'       => array_keys($pivot),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_values($pivot), 'color' => '#8e44ad']],
            'columnas'     => [['key' => 'item', 'label' => 'Ítem'], ['key' => 'total_cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    // ── CONTRATO ──────────────────────────────────────────────────────────────

    private function statsContrato(string $desde, string $hasta): array
    {
        return [
            $this->contrPorTipoContrato($desde, $hasta),
            $this->graficoEstados('contrato_serv_cab', 'contrato_fecha', 'contrato_estado', 'Contratos por Estado', '#2980b9', $desde, $hasta),
            $this->contrPorCondicion($desde, $hasta),
            $this->contrPorMes($desde, $hasta),
            $this->contrPorTipoServ($desde, $hasta),
        ];
    }

    private function contrPorTipoContrato(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(tc.tip_con_nombre, 'Sin tipo') AS tipo_contrato,
                   COUNT(*) AS cantidad
            FROM contrato_serv_cab csc
            LEFT JOIN tipo_contrato tc ON tc.id = csc.tipo_contrato_id
            WHERE csc.contrato_fecha BETWEEN :desde AND :hasta
            GROUP BY tc.tip_con_nombre ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'contr_tipo',
            'titulo'       => 'Contratos por Tipo',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->tipo_contrato, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [['key' => 'tipo_contrato', 'label' => 'Tipo de Contrato'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function contrPorCondicion(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(contrato_condicion_pago, 'Sin especificar') AS condicion,
                   COUNT(*) AS cantidad
            FROM contrato_serv_cab
            WHERE contrato_fecha BETWEEN :desde AND :hasta
            GROUP BY contrato_condicion_pago ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'contr_condicion',
            'titulo'       => 'Contratos por Condición de Pago',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->condicion, $data),
            'datasets'     => [['label' => 'Contratos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#f39c12']],
            'columnas'     => [['key' => 'condicion', 'label' => 'Condición'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function contrPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE contrato_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', contrato_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM contrato_serv_cab
            {$where}
            GROUP BY DATE_TRUNC('month', contrato_fecha)
            ORDER BY DATE_TRUNC('month', contrato_fecha)
        ", $params);

        return [
            'id'           => 'contr_mes',
            'titulo'       => 'Contratos por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Contratos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#2c3e50']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function contrPorTipoServ(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE csc.contrato_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND csc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT COALESCE(ts.tipo_serv_nombre, 'Sin tipo') AS tipo_servicio,
                   COUNT(*) AS cantidad
            FROM contrato_serv_cab csc
            LEFT JOIN tipo_servicio ts ON ts.id = csc.tipo_servicio_id
            {$where}
            GROUP BY ts.tipo_serv_nombre ORDER BY cantidad DESC
        ", $params);

        return [
            'id'           => 'contr_tipo_serv',
            'titulo'       => 'Contratos por Tipo de Servicio',
            'tipo_grafico' => 'bar',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->tipo_servicio, $data),
            'datasets'     => [['label' => 'Contratos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#27ae60']],
            'columnas'     => [['key' => 'tipo_servicio', 'label' => 'Tipo de Servicio'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    // ── RECLAMO ───────────────────────────────────────────────────────────────

    private function statsReclamo(string $desde, string $hasta): array
    {
        return [
            $this->rclPorPrioridad($desde, $hasta),
            $this->graficoEstados('reclamo_cli_cab', 'rec_cli_cab_fecha', 'rec_cli_cab_estado', 'Reclamos por Estado', '#e74c3c', $desde, $hasta),
            $this->rclPorMes($desde, $hasta),
            $this->rclTopClientes($desde, $hasta),
            $this->rclPorSucursal($desde, $hasta),
        ];
    }

    private function rclPorPrioridad(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(rec_cli_cab_prioridad, 'Sin prioridad') AS prioridad,
                   COUNT(*) AS cantidad
            FROM reclamo_cli_cab
            WHERE rec_cli_cab_fecha BETWEEN :desde AND :hasta
            GROUP BY rec_cli_cab_prioridad ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'rcl_prioridad',
            'titulo'       => 'Reclamos por Prioridad',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->prioridad, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [['key' => 'prioridad', 'label' => 'Prioridad'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function rclPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE rec_cli_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', rec_cli_cab_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM reclamo_cli_cab
            {$where}
            GROUP BY DATE_TRUNC('month', rec_cli_cab_fecha)
            ORDER BY DATE_TRUNC('month', rec_cli_cab_fecha)
        ", $params);

        return [
            'id'           => 'rcl_mes',
            'titulo'       => 'Reclamos por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Reclamos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#e74c3c']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function rclTopClientes(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $where  = "WHERE rcc.rec_cli_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND rcc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT COALESCE(c.cli_nombre || ' ' || c.cli_apellido, 'Sin cliente') AS cliente,
                   COUNT(*) AS cantidad
            FROM reclamo_cli_cab rcc
            JOIN clientes c ON c.id = rcc.clientes_id
            {$where}
            GROUP BY c.cli_nombre, c.cli_apellido
            ORDER BY cantidad DESC LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'rcl_top_clientes',
            'titulo'       => 'Top ' . $topN . ' Clientes con Más Reclamos',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->cliente, $data),
            'datasets'     => [['label' => 'Reclamos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#c0392b']],
            'columnas'     => [['key' => 'cliente', 'label' => 'Cliente'], ['key' => 'cantidad', 'label' => 'Reclamos']],
            'tabla'        => $data,
        ];
    }

    private function rclPorSucursal(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(s.suc_razon_social, 'Sin sucursal') AS sucursal,
                   COUNT(*) AS cantidad
            FROM reclamo_cli_cab rcc
            LEFT JOIN sucursal s ON s.id = rcc.sucursal_id
            WHERE rcc.rec_cli_cab_fecha BETWEEN :desde AND :hasta
            GROUP BY s.suc_razon_social ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'rcl_sucursal',
            'titulo'       => 'Reclamos por Área (Sucursal)',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->sucursal, $data),
            'datasets'     => [['label' => 'Reclamos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#8e44ad']],
            'columnas'     => [['key' => 'sucursal', 'label' => 'Área / Sucursal'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    // ── PROMOCIONES ───────────────────────────────────────────────────────────

    private function statsPromociones(string $desde, string $hasta): array
    {
        return [
            $this->promPorTipo($desde, $hasta),
            $this->graficoEstados('promociones_cab', 'prom_cab_fecha_registro', 'prom_cab_estado', 'Promociones por Estado', '#2980b9', $desde, $hasta),
            $this->promPorMes($desde, $hasta),
            $this->promTopItems($desde, $hasta),
        ];
    }

    private function promPorTipo(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(tp.tipo_prom_nombre, 'Sin tipo') AS tipo_promocion,
                   COUNT(*) AS cantidad
            FROM promociones_cab pc
            LEFT JOIN tipo_promociones tp ON tp.id = pc.tipo_promociones_id
            WHERE pc.prom_cab_fecha_registro BETWEEN :desde AND :hasta
            GROUP BY tp.tipo_prom_nombre ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'prom_tipo',
            'titulo'       => 'Promociones por Tipo',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->tipo_promocion, $data),
            'datasets'     => [['label' => 'Promociones', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#1abc9c']],
            'columnas'     => [['key' => 'tipo_promocion', 'label' => 'Tipo de Promoción'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function promPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE prom_cab_fecha_registro BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', prom_cab_fecha_registro), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM promociones_cab
            {$where}
            GROUP BY DATE_TRUNC('month', prom_cab_fecha_registro)
            ORDER BY DATE_TRUNC('month', prom_cab_fecha_registro)
        ", $params);

        return [
            'id'           => 'prom_mes',
            'titulo'       => 'Promociones por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Promociones', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#1abc9c']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function promTopItems(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE pc.prom_cab_fecha_registro BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND pc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   COUNT(DISTINCT pc.id) AS cantidad_promociones,
                   SUM(pd.prom_det_cantidad) AS total_cantidad
            FROM promociones_det pd
            JOIN promociones_cab pc ON pc.id = pd.promociones_cab_id
            JOIN items          i   ON i.id  = pd.item_id
            {$where}
            GROUP BY i.item_decripcion
            ORDER BY cantidad_promociones DESC LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'prom_items',
            'titulo'       => 'Top ' . $topN . ' Ítems Más Promovidos',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->item, $data),
            'datasets'     => [['label' => 'Promociones', 'data' => array_map(fn($r) => (int)$r->cantidad_promociones, $data), 'color' => '#f39c12']],
            'columnas'     => [
                ['key' => 'item',                'label' => 'Ítem'],
                ['key' => 'cantidad_promociones', 'label' => 'Nro. Promociones'],
                ['key' => 'total_cantidad',       'label' => 'Cantidad Total'],
            ],
            'tabla' => $data,
        ];
    }

    // ── DESCUENTOS ────────────────────────────────────────────────────────────

    private function statsDescuentos(string $desde, string $hasta): array
    {
        return [
            $this->descPorTipo($desde, $hasta),
            $this->graficoEstados('descuentos_cab', 'desc_cab_fecha_registro', 'desc_cab_estado', 'Descuentos por Estado', '#2980b9', $desde, $hasta),
            $this->descPorMes($desde, $hasta),
            $this->descPorRango($desde, $hasta),
        ];
    }

    private function descPorTipo(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(td.tipo_desc_nombre, 'Sin tipo') AS tipo_descuento,
                   COUNT(*) AS cantidad
            FROM descuentos_cab dc
            LEFT JOIN tipo_descuentos td ON td.id = dc.tipo_descuentos_id
            WHERE dc.desc_cab_fecha_registro BETWEEN :desde AND :hasta
            GROUP BY td.tipo_desc_nombre ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'desc_tipo',
            'titulo'       => 'Descuentos por Tipo',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->tipo_descuento, $data),
            'datasets'     => [['label' => 'Descuentos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#e74c3c']],
            'columnas'     => [['key' => 'tipo_descuento', 'label' => 'Tipo de Descuento'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function descPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE desc_cab_fecha_registro BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', desc_cab_fecha_registro), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM descuentos_cab
            {$where}
            GROUP BY DATE_TRUNC('month', desc_cab_fecha_registro)
            ORDER BY DATE_TRUNC('month', desc_cab_fecha_registro)
        ", $params);

        return [
            'id'           => 'desc_mes',
            'titulo'       => 'Descuentos por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Descuentos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#c0392b']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function descPorRango(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT CASE
                       WHEN COALESCE(desc_cab_porcentaje, 0) < 10  THEN 'Hasta 10%'
                       WHEN COALESCE(desc_cab_porcentaje, 0) < 20  THEN '10% - 20%'
                       WHEN COALESCE(desc_cab_porcentaje, 0) < 30  THEN '20% - 30%'
                       ELSE 'Más de 30%'
                   END AS rango,
                   COUNT(*) AS cantidad
            FROM descuentos_cab
            WHERE desc_cab_fecha_registro BETWEEN :desde AND :hasta
            GROUP BY rango
            ORDER BY MIN(COALESCE(desc_cab_porcentaje, 0))
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'desc_rango',
            'titulo'       => 'Descuentos por Rango de Porcentaje',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->rango, $data),
            'datasets'     => [['label' => 'Descuentos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#d35400']],
            'columnas'     => [['key' => 'rango', 'label' => 'Rango'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }
}
