<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformeGerencialVentasController extends Controller
{
    private function validarFechas(Request $r): void
    {
        $r->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);
    }

    // ── Helper genérico ───────────────────────────────────────────────────────

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
            'ventas'               => $this->statsVentas($desde, $hasta),
            'pedido_ventas'        => $this->statsPedidoVentas($desde, $hasta),
            'nota_remi_vent'       => $this->statsNotaRemiVent($desde, $hasta),
            'notas_vent'           => $this->statsNotasVent($desde, $hasta),
            'cobros'               => $this->statsCobros($desde, $hasta),
            'libro_ventas'         => $this->statsLibroVentas($desde, $hasta),
            'apertura_cierre_caja' => $this->statsAperturaCierreCaja($desde, $hasta),
            'arqueo_caja'          => $this->statsArqueoCaja($desde, $hasta),
            'recaudaciones'        => $this->statsRecaudaciones($desde, $hasta),
            default                => [],
        };

        $tieneFiltros = collect($secciones)->contains(fn($s) => !empty($s['filtros']));
        $catalogos    = [];
        if ($tieneFiltros) {
            $catalogos['sucursales'] = DB::select(
                "SELECT id, suc_razon_social AS nombre FROM sucursal ORDER BY suc_razon_social"
            );
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
            'ventas::vent_mes'             => $this->ventPorMes($desde, $hasta, $sucId),
            'ventas::vent_top_clientes'    => $this->ventTopClientes($desde, $hasta, $sucId),
            'ventas::vent_items'           => $this->ventTopItems($desde, $hasta, $sucId),
            'pedido_ventas::pv_mes'        => $this->pvPorMes($desde, $hasta, $sucId),
            'pedido_ventas::pv_clientes'   => $this->pvTopClientes($desde, $hasta, $sucId),
            'nota_remi_vent::nrv_mes'      => $this->nrvPorMes($desde, $hasta, $sucId),
            'nota_remi_vent::nrv_items'    => $this->nrvTopItems($desde, $hasta, $sucId),
            'notas_vent::nvc_mes'          => $this->nvcPorMes($desde, $hasta, $sucId),
            'notas_vent::nvc_clientes'     => $this->nvcTopClientes($desde, $hasta, $sucId),
            'cobros::cob_mes'                          => $this->cobPorMes($desde, $hasta, $sucId),
            'cobros::cob_clientes'                     => $this->cobTopClientes($desde, $hasta, $sucId),
            'apertura_cierre_caja::acc_mes'            => $this->accPorMes($desde, $hasta, $sucId),
            'apertura_cierre_caja::acc_estado'         => $this->accPorEstado($desde, $hasta, $sucId),
            'arqueo_caja::arq_mes'                     => $this->arqPorMes($desde, $hasta, $sucId),
            'arqueo_caja::arq_tipo'                    => $this->arqPorTipo($desde, $hasta, $sucId),
            'recaudaciones::rec_mes'                   => $this->recPorMes($desde, $hasta, $sucId),
            'recaudaciones::rec_metodo'                => $this->recPorMetodo($desde, $hasta, $sucId),
            default                                    => null,
        };

        if (!$result) {
            return response()->json(['error' => 'Sección no encontrada'], 404);
        }

        return response()->json(['seccion' => $result]);
    }

    // ── VENTAS ────────────────────────────────────────────────────────────────

    private function statsVentas(string $desde, string $hasta): array
    {
        return [
            $this->ventPorCondicion($desde, $hasta),
            $this->graficoEstados('ventas_cab', 'vent_fecha', 'vent_estado', 'Ventas por Estado', '#2980b9', $desde, $hasta),
            $this->ventPorMes($desde, $hasta),
            $this->ventTopClientes($desde, $hasta),
            $this->ventTopItems($desde, $hasta),
        ];
    }

    private function ventPorCondicion(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(condicion_pago, 'Sin especificar') AS condicion,
                   COUNT(*) AS cantidad
            FROM ventas_cab
            WHERE vent_fecha BETWEEN :desde AND :hasta
            GROUP BY condicion_pago ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'vent_condicion',
            'titulo'       => 'Ventas por Condición de Pago',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->condicion, $data),
            'datasets'     => [['label' => 'Ventas', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [['key' => 'condicion', 'label' => 'Condición'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function ventPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE vent_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', vent_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM ventas_cab
            {$where}
            GROUP BY DATE_TRUNC('month', vent_fecha)
            ORDER BY DATE_TRUNC('month', vent_fecha)
        ", $params);

        return [
            'id'           => 'vent_mes',
            'titulo'       => 'Ventas por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Ventas', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#27ae60']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function ventTopClientes(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $where  = "WHERE vc.vent_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND vc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT COALESCE(c.cli_nombre || ' ' || c.cli_apellido, 'Sin cliente') AS cliente,
                   COUNT(*) AS cantidad
            FROM ventas_cab vc
            JOIN clientes c ON c.id = vc.clientes_id
            {$where}
            GROUP BY c.cli_nombre, c.cli_apellido
            ORDER BY cantidad DESC LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'vent_top_clientes',
            'titulo'       => 'Top ' . $topN . ' Clientes con Más Compras',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->cliente, $data),
            'datasets'     => [['label' => 'Ventas', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#e74c3c']],
            'columnas'     => [['key' => 'cliente', 'label' => 'Cliente'], ['key' => 'cantidad', 'label' => 'Ventas']],
            'tabla'        => $data,
        ];
    }

    private function ventTopItems(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE vc.vent_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND vc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   SUM(vd.vent_det_cantidad) AS total_cantidad
            FROM ventas_det vd
            JOIN ventas_cab vc ON vc.id = vd.ventas_cab_id
            JOIN items      i  ON i.id  = vd.item_id
            {$where}
            GROUP BY i.item_decripcion
            ORDER BY total_cantidad DESC LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'vent_items',
            'titulo'       => 'Top ' . $topN . ' Ítems Más Vendidos',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->item, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (float)$r->total_cantidad, $data), 'color' => '#8e44ad']],
            'columnas'     => [['key' => 'item', 'label' => 'Ítem'], ['key' => 'total_cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    // ── PEDIDO VENTAS ─────────────────────────────────────────────────────────

    private function statsPedidoVentas(string $desde, string $hasta): array
    {
        return [
            $this->graficoEstados('pedidos_ventas', 'ped_ven_fecha', 'ped_ven_estado', 'Pedidos por Estado', '#2980b9', $desde, $hasta),
            $this->pvPorMes($desde, $hasta),
            $this->pvTopClientes($desde, $hasta),
            $this->pvPorSucursal($desde, $hasta),
        ];
    }

    private function pvPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE ped_ven_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', ped_ven_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM pedidos_ventas
            {$where}
            GROUP BY DATE_TRUNC('month', ped_ven_fecha)
            ORDER BY DATE_TRUNC('month', ped_ven_fecha)
        ", $params);

        return [
            'id'           => 'pv_mes',
            'titulo'       => 'Pedidos de Venta por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Pedidos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#f39c12']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function pvTopClientes(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $where  = "WHERE pv.ped_ven_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND pv.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT COALESCE(c.cli_nombre || ' ' || c.cli_apellido, 'Sin cliente') AS cliente,
                   COUNT(*) AS cantidad
            FROM pedidos_ventas pv
            JOIN clientes c ON c.id = pv.clientes_id
            {$where}
            GROUP BY c.cli_nombre, c.cli_apellido
            ORDER BY cantidad DESC LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'pv_clientes',
            'titulo'       => 'Top ' . $topN . ' Clientes con Más Pedidos',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->cliente, $data),
            'datasets'     => [['label' => 'Pedidos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#1abc9c']],
            'columnas'     => [['key' => 'cliente', 'label' => 'Cliente'], ['key' => 'cantidad', 'label' => 'Pedidos']],
            'tabla'        => $data,
        ];
    }

    private function pvPorSucursal(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(s.suc_razon_social, 'Sin sucursal') AS sucursal,
                   COUNT(*) AS cantidad
            FROM pedidos_ventas pv
            LEFT JOIN sucursal s ON s.id = pv.sucursal_id
            WHERE pv.ped_ven_fecha BETWEEN :desde AND :hasta
            GROUP BY s.suc_razon_social ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'pv_sucursal',
            'titulo'       => 'Pedidos de Venta por Sucursal',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->sucursal, $data),
            'datasets'     => [['label' => 'Pedidos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#d35400']],
            'columnas'     => [['key' => 'sucursal', 'label' => 'Sucursal'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    // ── NOTA DE REMISIÓN VENTAS ───────────────────────────────────────────────

    private function statsNotaRemiVent(string $desde, string $hasta): array
    {
        return [
            $this->graficoEstados('nota_remi_vent', 'nota_remi_vent_fecha', 'nota_remi_vent_estado', 'Remisiones por Estado', '#2980b9', $desde, $hasta),
            $this->nrvPorMes($desde, $hasta),
            $this->nrvTopItems($desde, $hasta),
            $this->nrvPorSucursal($desde, $hasta),
        ];
    }

    private function nrvPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE nota_remi_vent_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', nota_remi_vent_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM nota_remi_vent
            {$where}
            GROUP BY DATE_TRUNC('month', nota_remi_vent_fecha)
            ORDER BY DATE_TRUNC('month', nota_remi_vent_fecha)
        ", $params);

        return [
            'id'           => 'nrv_mes',
            'titulo'       => 'Remisiones de Venta por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Remisiones', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#16a085']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function nrvTopItems(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE nrv.nota_remi_vent_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND nrv.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   SUM(d.nr_vent_det_cantidad) AS total_cantidad
            FROM nota_remi_vent_det d
            JOIN nota_remi_vent nrv ON nrv.id = d.nota_remi_vent_id
            JOIN items          i   ON i.id   = d.item_id
            {$where}
            GROUP BY i.item_decripcion
            ORDER BY total_cantidad DESC LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'nrv_items',
            'titulo'       => 'Top ' . $topN . ' Ítems Más Remitidos',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->item, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (float)$r->total_cantidad, $data), 'color' => '#2980b9']],
            'columnas'     => [['key' => 'item', 'label' => 'Ítem'], ['key' => 'total_cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function nrvPorSucursal(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(s.suc_razon_social, 'Sin sucursal') AS sucursal,
                   COUNT(*) AS cantidad
            FROM nota_remi_vent nrv
            LEFT JOIN sucursal s ON s.id = nrv.sucursal_id
            WHERE nrv.nota_remi_vent_fecha BETWEEN :desde AND :hasta
            GROUP BY s.suc_razon_social ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'nrv_sucursal',
            'titulo'       => 'Remisiones de Venta por Sucursal',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->sucursal, $data),
            'datasets'     => [['label' => 'Remisiones', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#8e44ad']],
            'columnas'     => [['key' => 'sucursal', 'label' => 'Sucursal'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    // ── NOTAS DE VENTA ────────────────────────────────────────────────────────

    private function statsNotasVent(string $desde, string $hasta): array
    {
        return [
            $this->nvcPorTipo($desde, $hasta),
            $this->graficoEstados('notas_vent_cab', 'nota_vent_fecha', 'nota_vent_estado', 'Notas de Venta por Estado', '#2980b9', $desde, $hasta),
            $this->nvcPorMes($desde, $hasta),
            $this->nvcTopClientes($desde, $hasta),
        ];
    }

    private function nvcPorTipo(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(nota_vent_tipo, 'Sin tipo') AS tipo,
                   COUNT(*) AS cantidad
            FROM notas_vent_cab
            WHERE nota_vent_fecha BETWEEN :desde AND :hasta
            GROUP BY nota_vent_tipo ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'nvc_tipo',
            'titulo'       => 'Notas de Venta por Tipo (Crédito / Débito)',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->tipo, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [['key' => 'tipo', 'label' => 'Tipo'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function nvcPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE nota_vent_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', nota_vent_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM notas_vent_cab
            {$where}
            GROUP BY DATE_TRUNC('month', nota_vent_fecha)
            ORDER BY DATE_TRUNC('month', nota_vent_fecha)
        ", $params);

        return [
            'id'           => 'nvc_mes',
            'titulo'       => 'Notas de Venta por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Notas', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#c0392b']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function nvcTopClientes(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $where  = "WHERE nvc.nota_vent_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND nvc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT COALESCE(c.cli_nombre || ' ' || c.cli_apellido, 'Sin cliente') AS cliente,
                   COUNT(*) AS cantidad
            FROM notas_vent_cab nvc
            JOIN clientes c ON c.id = nvc.clientes_id
            {$where}
            GROUP BY c.cli_nombre, c.cli_apellido
            ORDER BY cantidad DESC LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'nvc_clientes',
            'titulo'       => 'Top ' . $topN . ' Clientes en Notas de Venta',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->cliente, $data),
            'datasets'     => [['label' => 'Notas', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#e67e22']],
            'columnas'     => [['key' => 'cliente', 'label' => 'Cliente'], ['key' => 'cantidad', 'label' => 'Notas']],
            'tabla'        => $data,
        ];
    }

    // ── COBROS ────────────────────────────────────────────────────────────────

    private function statsCobros(string $desde, string $hasta): array
    {
        return [
            $this->cobPorForma($desde, $hasta),
            $this->graficoEstados('cobros_cab', 'cobro_fecha', 'cobro_estado', 'Cobros por Estado', '#27ae60', $desde, $hasta),
            $this->cobPorMes($desde, $hasta),
            $this->cobTopClientes($desde, $hasta),
        ];
    }

    private function cobPorForma(string $desde, string $hasta): array
    {
        // Consulta las 5 subtablas de medios de pago con UNION ALL
        // ya que cobros_cab.forma_cobro_id solo refleja el método principal
        $data = DB::select("
            SELECT forma,
                   COUNT(*)                       AS cantidad,
                   COALESCE(SUM(total_monto), 0)  AS total_monto
            FROM (
                SELECT 'EFECTIVO'      AS forma, ce.monto_efectivo      AS total_monto
                FROM cobro_efectivo ce
                JOIN cobros_cab cc ON cc.id = ce.cobros_cab_id
                WHERE cc.cobro_fecha BETWEEN :desde1 AND :hasta1

                UNION ALL

                SELECT 'TARJETA'       AS forma, ct.monto_tarjeta       AS total_monto
                FROM cobros_tarjeta ct
                JOIN cobros_cab cc ON cc.id = ct.cobros_cab_id
                WHERE cc.cobro_fecha BETWEEN :desde2 AND :hasta2

                UNION ALL

                SELECT 'CHEQUE'        AS forma, ch.monto_cheque        AS total_monto
                FROM cobros_cheque ch
                JOIN cobros_cab cc ON cc.id = ch.cobros_cab_id
                WHERE cc.cobro_fecha BETWEEN :desde3 AND :hasta3

                UNION ALL

                SELECT 'TRANSFERENCIA' AS forma, tr.monto_transferencia AS total_monto
                FROM cobros_transferencia tr
                JOIN cobros_cab cc ON cc.id = tr.cobros_cab_id
                WHERE cc.cobro_fecha BETWEEN :desde4 AND :hasta4

                UNION ALL

                SELECT 'QR'            AS forma, qr.monto_qr            AS total_monto
                FROM cobros_qr qr
                JOIN cobros_cab cc ON cc.id = qr.cobros_cab_id
                WHERE cc.cobro_fecha BETWEEN :desde5 AND :hasta5
            ) sub
            GROUP BY forma
            ORDER BY total_monto DESC
        ", [
            'desde1' => $desde, 'hasta1' => $hasta,
            'desde2' => $desde, 'hasta2' => $hasta,
            'desde3' => $desde, 'hasta3' => $hasta,
            'desde4' => $desde, 'hasta4' => $hasta,
            'desde5' => $desde, 'hasta5' => $hasta,
        ]);

        return [
            'id'           => 'cob_forma',
            'titulo'       => 'Cobros por Forma de Pago',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->forma, $data),
            'datasets'     => [['label' => 'Monto (Gs.)', 'data' => array_map(fn($r) => (float)$r->total_monto, $data)]],
            'columnas'     => [
                ['key' => 'forma',       'label' => 'Forma de Pago'],
                ['key' => 'cantidad',    'label' => 'Registros'],
                ['key' => 'total_monto', 'label' => 'Monto (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function cobPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE cobro_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', cobro_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad,
                   COALESCE(SUM(cobro_importe), 0) AS total_monto
            FROM cobros_cab
            {$where}
            GROUP BY DATE_TRUNC('month', cobro_fecha)
            ORDER BY DATE_TRUNC('month', cobro_fecha)
        ", $params);

        return [
            'id'           => 'cob_mes',
            'titulo'       => 'Cobros por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Cobros', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#27ae60']],
            'columnas'     => [
                ['key' => 'mes',         'label' => 'Mes'],
                ['key' => 'cantidad',    'label' => 'Cantidad'],
                ['key' => 'total_monto', 'label' => 'Total (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function cobTopClientes(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $where  = "WHERE cc.cobro_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND cc.sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT COALESCE(c.cli_nombre || ' ' || c.cli_apellido, 'Sin cliente') AS cliente,
                   COUNT(*) AS cantidad,
                   COALESCE(SUM(cc.cobro_importe), 0) AS total_monto
            FROM cobros_cab cc
            JOIN clientes c ON c.id = cc.clientes_id
            {$where}
            GROUP BY c.cli_nombre, c.cli_apellido
            ORDER BY total_monto DESC LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'cob_clientes',
            'titulo'       => 'Top ' . $topN . ' Clientes por Monto Cobrado',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->cliente, $data),
            'datasets'     => [['label' => 'Monto (Gs.)', 'data' => array_map(fn($r) => (float)$r->total_monto, $data), 'color' => '#16a085']],
            'columnas'     => [
                ['key' => 'cliente',     'label' => 'Cliente'],
                ['key' => 'cantidad',    'label' => 'Cobros'],
                ['key' => 'total_monto', 'label' => 'Total (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    // ── LIBRO DE VENTAS ───────────────────────────────────────────────────────

    private function statsLibroVentas(string $desde, string $hasta): array
    {
        return [
            $this->lvPorImpuesto($desde, $hasta),
            $this->lvPorCondicion($desde, $hasta),
            $this->lvPorMes($desde, $hasta),
            $this->lvTopClientes($desde, $hasta),
        ];
    }

    private function lvPorImpuesto(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(tip_imp_nom, 'Sin impuesto') AS impuesto,
                   COUNT(*) AS cantidad,
                   COALESCE(SUM(\"libV_monto\"), 0) AS total_monto
            FROM libro_ventas
            WHERE \"libV_fecha\" BETWEEN :desde AND :hasta
            GROUP BY tip_imp_nom ORDER BY total_monto DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'lv_impuesto',
            'titulo'       => 'Libro de Ventas por Tipo de Impuesto',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->impuesto, $data),
            'datasets'     => [['label' => 'Monto', 'data' => array_map(fn($r) => (float)$r->total_monto, $data)]],
            'columnas'     => [
                ['key' => 'impuesto',    'label' => 'Tipo Impuesto'],
                ['key' => 'cantidad',    'label' => 'Registros'],
                ['key' => 'total_monto', 'label' => 'Total (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function lvPorCondicion(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(condicion_pago, 'Sin especificar') AS condicion,
                   COUNT(*) AS cantidad,
                   COALESCE(SUM(\"libV_monto\"), 0) AS total_monto
            FROM libro_ventas
            WHERE \"libV_fecha\" BETWEEN :desde AND :hasta
            GROUP BY condicion_pago ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'lv_condicion',
            'titulo'       => 'Libro de Ventas por Condición de Pago',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->condicion, $data),
            'datasets'     => [['label' => 'Registros', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#2980b9']],
            'columnas'     => [
                ['key' => 'condicion',   'label' => 'Condición'],
                ['key' => 'cantidad',    'label' => 'Registros'],
                ['key' => 'total_monto', 'label' => 'Total (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function lvPorMes(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', \"libV_fecha\"), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad,
                   COALESCE(SUM(\"libV_monto\"), 0) AS total_monto
            FROM libro_ventas
            WHERE \"libV_fecha\" BETWEEN :desde AND :hasta
            GROUP BY DATE_TRUNC('month', \"libV_fecha\")
            ORDER BY DATE_TRUNC('month', \"libV_fecha\")
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'lv_mes',
            'titulo'       => 'Libro de Ventas por Mes',
            'tipo_grafico' => 'line',
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Registros', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#e74c3c']],
            'columnas'     => [
                ['key' => 'mes',         'label' => 'Mes'],
                ['key' => 'cantidad',    'label' => 'Registros'],
                ['key' => 'total_monto', 'label' => 'Total (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function lvTopClientes(string $desde, string $hasta, int $topN = 10): array
    {
        $topN = min(50, max(1, $topN));
        $data = DB::select("
            SELECT COALESCE(cli_nombre || ' ' || cli_apellido, 'Sin cliente') AS cliente,
                   COUNT(*) AS cantidad,
                   COALESCE(SUM(\"libV_monto\"), 0) AS total_monto
            FROM libro_ventas
            WHERE \"libV_fecha\" BETWEEN :desde AND :hasta
              AND cli_nombre IS NOT NULL
            GROUP BY cli_nombre, cli_apellido
            ORDER BY total_monto DESC LIMIT {$topN}
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'lv_clientes',
            'titulo'       => 'Top ' . $topN . ' Clientes por Monto en Libro de Ventas',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'labels'       => array_map(fn($r) => $r->cliente, $data),
            'datasets'     => [['label' => 'Monto (Gs.)', 'data' => array_map(fn($r) => (float)$r->total_monto, $data), 'color' => '#8e44ad']],
            'columnas'     => [
                ['key' => 'cliente',     'label' => 'Cliente'],
                ['key' => 'cantidad',    'label' => 'Registros'],
                ['key' => 'total_monto', 'label' => 'Total (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    // ── APERTURA / CIERRE DE CAJA ─────────────────────────────────────────────

    private function statsAperturaCierreCaja(string $desde, string $hasta): array
    {
        return [
            $this->accPorEstado($desde, $hasta),
            $this->accPorMes($desde, $hasta),
            $this->accPorSucursal($desde, $hasta),
        ];
    }

    private function accPorEstado(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE fecha_apertura BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT COALESCE(estado, 'Sin estado') AS estado, COUNT(*) AS cantidad
            FROM apertura_cierre_caja
            {$where}
            GROUP BY estado ORDER BY cantidad DESC
        ", $params);

        return [
            'id'           => 'acc_estado',
            'titulo'       => 'Aperturas/Cierres por Estado',
            'tipo_grafico' => 'doughnut',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->estado, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [['key' => 'estado', 'label' => 'Estado'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function accPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE fecha_apertura BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) { $where .= " AND sucursal_id = :sucursal_id"; $params['sucursal_id'] = $sucursalId; }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', fecha_apertura), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM apertura_cierre_caja
            {$where}
            GROUP BY DATE_TRUNC('month', fecha_apertura)
            ORDER BY DATE_TRUNC('month', fecha_apertura)
        ", $params);

        return [
            'id'           => 'acc_mes',
            'titulo'       => 'Aperturas/Cierres de Caja por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Aperturas', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#2980b9']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function accPorSucursal(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(s.suc_razon_social, 'Sin sucursal') AS sucursal,
                   COUNT(*) AS cantidad
            FROM apertura_cierre_caja acc
            LEFT JOIN sucursal s ON s.id = acc.sucursal_id
            WHERE acc.fecha_apertura BETWEEN :desde AND :hasta
            GROUP BY s.suc_razon_social ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'acc_sucursal',
            'titulo'       => 'Aperturas/Cierres por Sucursal',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->sucursal, $data),
            'datasets'     => [['label' => 'Aperturas', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#1abc9c']],
            'columnas'     => [['key' => 'sucursal', 'label' => 'Sucursal'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    // ── ARQUEO DE CAJA ────────────────────────────────────────────────────────

    private function statsArqueoCaja(string $desde, string $hasta): array
    {
        return [
            $this->arqPorTipo($desde, $hasta),
            $this->arqPorMes($desde, $hasta),
            $this->arqPorSucursal($desde, $hasta),
        ];
    }

    private function arqPorTipo(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $join   = $sucursalId ? "JOIN apertura_cierre_caja acc ON acc.id = a.apertura_cierre_caja_id" : "";
        $where  = "WHERE a.arqueo_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $join   = "JOIN apertura_cierre_caja acc ON acc.id = a.apertura_cierre_caja_id";
            $where .= " AND acc.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        $data = DB::select("
            SELECT COALESCE(a.tipo_arqueo, 'Sin tipo') AS tipo_arqueo,
                   COUNT(*) AS cantidad
            FROM arqueo_caja a
            {$join}
            {$where}
            GROUP BY a.tipo_arqueo ORDER BY cantidad DESC
        ", $params);

        return [
            'id'           => 'arq_tipo',
            'titulo'       => 'Arqueos por Tipo',
            'tipo_grafico' => 'bar',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->tipo_arqueo, $data),
            'datasets'     => [['label' => 'Arqueos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#e67e22']],
            'columnas'     => [['key' => 'tipo_arqueo', 'label' => 'Tipo'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function arqPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $join   = "";
        $where  = "WHERE a.arqueo_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $join   = "JOIN apertura_cierre_caja acc ON acc.id = a.apertura_cierre_caja_id";
            $where .= " AND acc.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', a.arqueo_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM arqueo_caja a
            {$join}
            {$where}
            GROUP BY DATE_TRUNC('month', a.arqueo_fecha)
            ORDER BY DATE_TRUNC('month', a.arqueo_fecha)
        ", $params);

        return [
            'id'           => 'arq_mes',
            'titulo'       => 'Arqueos de Caja por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Arqueos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#9b59b6']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function arqPorSucursal(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(s.suc_razon_social, 'Sin sucursal') AS sucursal,
                   COUNT(*) AS cantidad
            FROM arqueo_caja a
            JOIN apertura_cierre_caja acc ON acc.id = a.apertura_cierre_caja_id
            LEFT JOIN sucursal s ON s.id = acc.sucursal_id
            WHERE a.arqueo_fecha BETWEEN :desde AND :hasta
            GROUP BY s.suc_razon_social ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'arq_sucursal',
            'titulo'       => 'Arqueos de Caja por Sucursal',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->sucursal, $data),
            'datasets'     => [['label' => 'Arqueos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#c0392b']],
            'columnas'     => [['key' => 'sucursal', 'label' => 'Sucursal'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    // ── RECAUDACIONES A DEPOSITAR ─────────────────────────────────────────────

    private function statsRecaudaciones(string $desde, string $hasta): array
    {
        return [
            $this->recPorMetodo($desde, $hasta),
            $this->recPorMes($desde, $hasta),
            $this->recPorSucursal($desde, $hasta),
        ];
    }

    private function recPorMetodo(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $join   = "";
        $where  = "WHERE rd.reca_dep_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $join   = "JOIN apertura_cierre_caja acc ON acc.id = rd.apertura_cierre_caja_id";
            $where .= " AND acc.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        $data = DB::select("
            SELECT COALESCE(rd.reca_dep_met_pago, 'Sin método') AS metodo,
                   COUNT(*) AS cantidad
            FROM recaudaciones_depositar rd
            {$join}
            {$where}
            GROUP BY rd.reca_dep_met_pago ORDER BY cantidad DESC
        ", $params);

        return [
            'id'           => 'rec_metodo',
            'titulo'       => 'Recaudaciones por Método de Pago',
            'tipo_grafico' => 'bar',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->metodo, $data),
            'datasets'     => [['label' => 'Recaudaciones', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#27ae60']],
            'columnas'     => [['key' => 'metodo', 'label' => 'Método'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function recPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $join   = "";
        $where  = "WHERE rd.reca_dep_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $join   = "JOIN apertura_cierre_caja acc ON acc.id = rd.apertura_cierre_caja_id";
            $where .= " AND acc.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', rd.reca_dep_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM recaudaciones_depositar rd
            {$join}
            {$where}
            GROUP BY DATE_TRUNC('month', rd.reca_dep_fecha)
            ORDER BY DATE_TRUNC('month', rd.reca_dep_fecha)
        ", $params);

        return [
            'id'           => 'rec_mes',
            'titulo'       => 'Recaudaciones por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [['id' => 'sucursal', 'label' => 'Sucursal']],
            'labels'       => array_map(fn($r) => $r->mes, $data),
            'datasets'     => [['label' => 'Recaudaciones', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#3498db']],
            'columnas'     => [['key' => 'mes', 'label' => 'Mes'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function recPorSucursal(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(s.suc_razon_social, 'Sin sucursal') AS sucursal,
                   COUNT(*) AS cantidad
            FROM recaudaciones_depositar rd
            JOIN apertura_cierre_caja acc ON acc.id = rd.apertura_cierre_caja_id
            LEFT JOIN sucursal s ON s.id = acc.sucursal_id
            WHERE rd.reca_dep_fecha BETWEEN :desde AND :hasta
            GROUP BY s.suc_razon_social ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'rec_sucursal',
            'titulo'       => 'Recaudaciones por Sucursal',
            'tipo_grafico' => 'bar',
            'labels'       => array_map(fn($r) => $r->sucursal, $data),
            'datasets'     => [['label' => 'Recaudaciones', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#d35400']],
            'columnas'     => [['key' => 'sucursal', 'label' => 'Sucursal'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }
}
