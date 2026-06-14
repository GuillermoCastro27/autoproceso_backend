<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformeGerencialComprasController extends Controller
{
    private function validarFechas(Request $r): void
    {
        $r->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);
    }

    // ── 1. Cuentas a pagar por proveedor ──────────────────────────────────────

    public function cuentasAPagar(Request $r)
    {
        $this->validarFechas($r);

        $where  = "WHERE cp.cta_pag_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $r->desde, 'hasta' => $r->hasta];

        if ($r->filled('condicion_pago')) {
            $where .= " AND UPPER(COALESCE(cp.condicion_pago, cc.condicion_pago)) = UPPER(:condicion_pago)";
            $params['condicion_pago'] = $r->condicion_pago;
        }
        if ($r->filled('estado')) {
            $where .= " AND cp.cta_pag_estado = :estado";
            $params['estado'] = $r->estado;
        }
        if ($r->filled('proveedor')) {
            $where .= " AND LOWER(prov.prov_razonsocial) ILIKE LOWER(:proveedor)";
            $params['proveedor'] = '%' . trim($r->proveedor) . '%';
        }

        // Detalle: agrupado por proveedor + condición + estado (para la tabla)
        $data = DB::select("
            SELECT
                COALESCE(prov.prov_razonsocial, 'Sin proveedor')    AS proveedor,
                COALESCE(prov.prov_ruc, '-')                        AS ruc,
                COALESCE(cp.condicion_pago, cc.condicion_pago, '-') AS condicion_pago,
                cp.cta_pag_estado                                   AS estado,
                COUNT(cp.id)                                        AS cantidad_cuotas,
                SUM(cp.cta_pag_monto)                               AS monto_total,
                TO_CHAR(MIN(cp.cta_pag_fecha), 'dd/mm/yyyy')       AS primera_fecha,
                TO_CHAR(MAX(cp.cta_pag_fecha), 'dd/mm/yyyy')       AS ultima_fecha
            FROM ctas_pagar cp
            JOIN  compra_cab cc        ON cc.id   = cp.compra_cab_id
            LEFT JOIN proveedores prov ON prov.id = cc.proveedor_id
            {$where}
            GROUP BY prov.prov_razonsocial, prov.prov_ruc,
                     cp.condicion_pago, cc.condicion_pago,
                     cp.cta_pag_estado
            ORDER BY monto_total DESC NULLS LAST
        ", $params);

        // Pivote para el gráfico: un bar por proveedor (suma total)
        $pivot = [];
        foreach ($data as $row) {
            $k = $row->proveedor;
            $pivot[$k] = ($pivot[$k] ?? 0) + (float)$row->monto_total;
        }
        arsort($pivot);

        $total = array_sum($pivot);

        return response()->json([
            'titulo'   => 'Cuentas a Pagar por Proveedor',
            'labels'   => array_keys($pivot),
            'datasets' => [
                ['label' => 'Monto Total (Gs.)', 'data' => array_values($pivot), 'color' => '#2980b9'],
            ],
            'tabla'    => $data,
            'columnas' => [
                ['key' => 'proveedor',      'label' => 'Proveedor'],
                ['key' => 'ruc',            'label' => 'RUC'],
                ['key' => 'condicion_pago', 'label' => 'Condición'],
                ['key' => 'estado',         'label' => 'Estado'],
                ['key' => 'cantidad_cuotas','label' => 'Cuotas'],
                ['key' => 'monto_total',    'label' => 'Monto (Gs.)'],
                ['key' => 'primera_fecha',  'label' => 'Desde'],
                ['key' => 'ultima_fecha',   'label' => 'Hasta'],
            ],
            'totales' => [
                'Total General' => number_format($total, 0, ',', '.') . ' Gs.',
                'Proveedores'   => count($pivot),
            ],
        ]);
    }

    // ── 2. Ítems más comprados ────────────────────────────────────────────────

    public function itemsMasComprados(Request $r)
    {
        $this->validarFechas($r);

        $topN   = min(50, max(1, (int)($r->top_n ?? 10)));
        $where  = "WHERE cc.comp_fecha BETWEEN :desde AND :hasta AND cc.comp_estado NOT IN ('ANULADO')";
        $params = ['desde' => $r->desde, 'hasta' => $r->hasta];

        if ($r->filled('sucursal')) {
            $where .= " AND LOWER(s.suc_razon_social) ILIKE LOWER(:sucursal)";
            $params['sucursal'] = '%' . trim($r->sucursal) . '%';
        }
        if ($r->filled('deposito')) {
            $where .= " AND LOWER(d.dep_nombre) ILIKE LOWER(:deposito)";
            $params['deposito'] = '%' . trim($r->deposito) . '%';
        }

        $data = DB::select("
            SELECT
                i.item_decripcion                                          AS item,
                s.suc_razon_social                                         AS sucursal,
                COALESCE(d.dep_nombre, 'Sin depósito')                    AS deposito,
                SUM(cd.comp_det_cantidad)                                  AS total_cantidad,
                COALESCE(SUM(cd.comp_det_cantidad * cd.comp_det_costo), 0) AS total_monto
            FROM compra_det cd
            JOIN compra_cab cc ON cc.id  = cd.compra_cab_id
            JOIN items      i  ON i.id   = cd.item_id
            JOIN sucursal   s  ON s.id   = cc.sucursal_id
            LEFT JOIN deposito d ON d.id = cd.deposito_id
            {$where}
            GROUP BY i.item_decripcion, s.suc_razon_social, d.dep_nombre
            ORDER BY total_cantidad DESC
            LIMIT {$topN}
        ", $params);

        $labels     = array_map(fn($r) => $r->item, $data);
        $cantidades = array_map(fn($r) => (float)$r->total_cantidad, $data);
        $montos     = array_map(fn($r) => (float)$r->total_monto, $data);

        return response()->json([
            'titulo'   => 'Ítems Más Comprados',
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Cantidad', 'data' => $cantidades, 'color' => '#27ae60'],
                ['label' => 'Monto (Gs.)', 'data' => $montos,  'color' => '#2980b9'],
            ],
            'tabla'    => $data,
            'columnas' => [
                ['key' => 'item',            'label' => 'Ítem'],
                ['key' => 'sucursal',        'label' => 'Sucursal'],
                ['key' => 'deposito',        'label' => 'Depósito'],
                ['key' => 'total_cantidad',  'label' => 'Cantidad Total'],
                ['key' => 'total_monto',     'label' => 'Monto Total (Gs.)'],
            ],
            'totales'  => ['Registros' => count($data)],
        ]);
    }

    // ── 3. Ítems más transferidos (nota remisión compra) ──────────────────────

    public function itemsMasTransferidos(Request $r)
    {
        $this->validarFechas($r);

        $topN   = min(50, max(1, (int)($r->top_n ?? 10)));
        $where  = "WHERE nrc.nota_remi_fecha BETWEEN :desde AND :hasta AND nrc.nota_remi_estado = 'CONFIRMADO'";
        $params = ['desde' => $r->desde, 'hasta' => $r->hasta];

        if ($r->filled('dep_origen')) {
            $where .= " AND LOWER(d_orig.dep_nombre) ILIKE LOWER(:dep_origen)";
            $params['dep_origen'] = '%' . trim($r->dep_origen) . '%';
        }
        if ($r->filled('dep_destino')) {
            $where .= " AND LOWER(d_dest.dep_nombre) ILIKE LOWER(:dep_destino)";
            $params['dep_destino'] = '%' . trim($r->dep_destino) . '%';
        }

        $data = DB::select("
            SELECT
                i.item_decripcion                                       AS item,
                COALESCE(d_orig.dep_nombre, 'Sin origen')              AS deposito_origen,
                COALESCE(d_dest.dep_nombre, 'Sin destino')             AS deposito_destino,
                SUM(nrd.nota_remi_com_det_cantidad)                    AS total_transferido
            FROM nota_remi_com_det nrd
            JOIN nota_remi_comp nrc ON nrc.id  = nrd.nota_remi_comp_id
            JOIN items          i   ON i.id    = nrd.item_id
            LEFT JOIN deposito d_orig ON d_orig.id = nrd.deposito_id
            LEFT JOIN deposito d_dest ON d_dest.id = nrd.deposito_destino_id
            {$where}
            GROUP BY i.item_decripcion, d_orig.dep_nombre, d_dest.dep_nombre
            ORDER BY total_transferido DESC
            LIMIT {$topN}
        ", $params);

        $labels = array_map(fn($r) => $r->item, $data);
        $vals   = array_map(fn($r) => (float)$r->total_transferido, $data);

        return response()->json([
            'titulo'   => 'Ítems Más Transferidos entre Depósitos',
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Cantidad Transferida', 'data' => $vals, 'color' => '#8e44ad'],
            ],
            'tabla'    => $data,
            'columnas' => [
                ['key' => 'item',               'label' => 'Ítem'],
                ['key' => 'deposito_origen',    'label' => 'Depósito Origen'],
                ['key' => 'deposito_destino',   'label' => 'Depósito Destino'],
                ['key' => 'total_transferido',  'label' => 'Cantidad Transferida'],
            ],
            'totales' => ['Registros' => count($data)],
        ]);
    }

    // ── 4. Libro de compras por tipo de impuesto ──────────────────────────────

    public function libroComprasPorImpuesto(Request $r)
    {
        $this->validarFechas($r);

        $data = DB::select("
            SELECT
                COALESCE(lc.\"tip_imp_nom\", 'Sin impuesto') AS tipo_impuesto,
                COUNT(*)                                      AS cantidad,
                COALESCE(SUM(lc.\"libC_monto\"), 0)          AS monto_total
            FROM libro_compras lc
            WHERE lc.\"libC_fecha\" BETWEEN :desde AND :hasta
            GROUP BY lc.\"tip_imp_nom\"
            ORDER BY monto_total DESC
        ", ['desde' => $r->desde, 'hasta' => $r->hasta]);

        $labels = array_map(fn($r) => $r->tipo_impuesto, $data);
        $montos = array_map(fn($r) => (float)$r->monto_total, $data);
        $total  = array_sum($montos);

        return response()->json([
            'titulo'   => 'Libro de Compras por Tipo de Impuesto',
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Monto (Gs.)', 'data' => $montos, 'color' => null],
            ],
            'tabla'    => $data,
            'columnas' => [
                ['key' => 'tipo_impuesto', 'label' => 'Tipo de Impuesto'],
                ['key' => 'cantidad',      'label' => 'Cantidad de Compras'],
                ['key' => 'monto_total',   'label' => 'Monto Total (Gs.)'],
            ],
            'totales' => [
                'Total General' => number_format($total, 0, ',', '.') . ' Gs.',
                'Registros'     => count($data),
            ],
        ]);
    }

    // ── 5. Presupuestos aprobados por mes ─────────────────────────────────────

    public function presupuestosPorMes(Request $r)
    {
        $this->validarFechas($r);

        $where  = "WHERE p.pre_estado IN ('CONFIRMADO', 'PROCESADO') AND p.pre_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $r->desde, 'hasta' => $r->hasta];

        if ($r->filled('sucursal')) {
            $where .= " AND LOWER(s.suc_razon_social) ILIKE LOWER(:sucursal)";
            $params['sucursal'] = '%' . trim($r->sucursal) . '%';
        }

        $data = DB::select("
            SELECT
                TO_CHAR(DATE_TRUNC('month', p.pre_fecha), 'MM/YYYY') AS mes,
                DATE_TRUNC('month', p.pre_fecha)                     AS mes_orden,
                s.suc_razon_social                                   AS sucursal,
                COUNT(*)                                             AS cantidad
            FROM presupuestos p
            JOIN sucursal s ON s.id = p.sucursal_id
            {$where}
            GROUP BY DATE_TRUNC('month', p.pre_fecha), s.suc_razon_social
            ORDER BY mes_orden ASC
        ", $params);

        // Pivotear por sucursal para múltiples datasets
        $meses     = array_values(array_unique(array_column((array)json_decode(json_encode($data)), 'mes')));
        $sucursales = array_values(array_unique(array_column((array)json_decode(json_encode($data)), 'sucursal')));
        $colors    = ['#2980b9', '#27ae60', '#e74c3c', '#f39c12', '#8e44ad', '#16a085'];

        $datasets = [];
        foreach ($sucursales as $idx => $suc) {
            $valores = [];
            foreach ($meses as $mes) {
                $fila = collect($data)->first(fn($row) => $row->mes === $mes && $row->sucursal === $suc);
                $valores[] = $fila ? (int)$fila->cantidad : 0;
            }
            $datasets[] = [
                'label' => $suc,
                'data'  => $valores,
                'color' => $colors[$idx % count($colors)],
            ];
        }

        return response()->json([
            'titulo'   => 'Presupuestos Aprobados por Mes',
            'labels'   => $meses,
            'datasets' => $datasets,
            'tabla'    => $data,
            'columnas' => [
                ['key' => 'mes',      'label' => 'Mes'],
                ['key' => 'sucursal', 'label' => 'Sucursal'],
                ['key' => 'cantidad', 'label' => 'Cantidad'],
            ],
            'totales' => ['Total' => array_sum(array_column((array)json_decode(json_encode($data)), 'cantidad'))],
        ]);
    }

    // ── 6. Proveedor con más presupuesto aprobado ─────────────────────────────

    public function proveedorMasPresupuesto(Request $r)
    {
        $this->validarFechas($r);

        $topN   = min(50, max(1, (int)($r->top_n ?? 10)));
        $where  = "WHERE p.pre_estado IN ('CONFIRMADO', 'PROCESADO') AND p.pre_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $r->desde, 'hasta' => $r->hasta];

        $data = DB::select("
            SELECT
                prov.prov_razonsocial                                           AS proveedor,
                prov.prov_ruc                                                   AS ruc,
                COUNT(DISTINCT p.id)                                            AS cantidad_presupuestos,
                COALESCE(SUM(pd.det_cantidad * pd.det_costo), 0)               AS monto_total
            FROM presupuestos p
            JOIN proveedores           prov ON prov.id = p.proveedor_id
            LEFT JOIN presupuestos_detalles pd ON pd.presupuesto_id = p.id
            {$where}
            GROUP BY prov.id, prov.prov_razonsocial, prov.prov_ruc
            ORDER BY monto_total DESC
            LIMIT {$topN}
        ", $params);

        $labels = array_map(fn($r) => $r->proveedor, $data);
        $montos = array_map(fn($r) => (float)$r->monto_total, $data);

        return response()->json([
            'titulo'   => 'Proveedores con Más Presupuesto Aprobado',
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Monto Total (Gs.)', 'data' => $montos, 'color' => '#f39c12'],
            ],
            'tabla'    => $data,
            'columnas' => [
                ['key' => 'proveedor',             'label' => 'Proveedor'],
                ['key' => 'ruc',                   'label' => 'RUC'],
                ['key' => 'cantidad_presupuestos', 'label' => 'Presupuestos'],
                ['key' => 'monto_total',           'label' => 'Monto Total (Gs.)'],
            ],
            'totales' => ['Proveedores' => count($data)],
        ]);
    }

    // ── 7. Ajustes de inventario (Entrada/Salida por motivo) ──────────────────

    public function ajustesInventario(Request $r)
    {
        $this->validarFechas($r);

        $where  = "WHERE ac.ajus_cab_estado = 'CONFIRMADO' AND ac.ajus_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $r->desde, 'hasta' => $r->hasta];

        if ($r->filled('sucursal')) {
            $where .= " AND LOWER(s.suc_razon_social) ILIKE LOWER(:sucursal)";
            $params['sucursal'] = '%' . trim($r->sucursal) . '%';
        }
        if ($r->filled('tipo_ajuste')) {
            $where .= " AND ac.tipo_ajuste = :tipo_ajuste";
            $params['tipo_ajuste'] = $r->tipo_ajuste;
        }

        $data = DB::select("
            SELECT
                ma.descripcion          AS motivo,
                ac.tipo_ajuste,
                s.suc_razon_social      AS sucursal,
                COUNT(*)                AS cantidad
            FROM ajuste_cab ac
            JOIN motivo_ajuste ma ON ma.id = ac.motivo_ajuste_id
            JOIN sucursal      s  ON s.id  = ac.sucursal_id
            {$where}
            GROUP BY ma.descripcion, ac.tipo_ajuste, s.suc_razon_social
            ORDER BY ma.descripcion, ac.tipo_ajuste
        ", $params);

        // Pivotear Entrada/Salida por motivo
        $motivos  = array_values(array_unique(array_column((array)json_decode(json_encode($data)), 'motivo')));
        $entradas = [];
        $salidas  = [];
        foreach ($motivos as $m) {
            $e = collect($data)->first(fn($row) => $row->motivo === $m && $row->tipo_ajuste === 'Entrada');
            $s = collect($data)->first(fn($row) => $row->motivo === $m && $row->tipo_ajuste === 'Salida');
            $entradas[] = $e ? (int)$e->cantidad : 0;
            $salidas[]  = $s ? (int)$s->cantidad : 0;
        }

        return response()->json([
            'titulo'   => 'Ajustes de Inventario por Motivo',
            'labels'   => $motivos,
            'datasets' => [
                ['label' => 'Entrada', 'data' => $entradas, 'color' => '#27ae60'],
                ['label' => 'Salida',  'data' => $salidas,  'color' => '#e74c3c'],
            ],
            'tabla'    => $data,
            'columnas' => [
                ['key' => 'motivo',      'label' => 'Motivo'],
                ['key' => 'tipo_ajuste', 'label' => 'Tipo'],
                ['key' => 'sucursal',    'label' => 'Sucursal'],
                ['key' => 'cantidad',    'label' => 'Cantidad'],
            ],
            'totales' => [
                'Total Entradas' => array_sum($entradas),
                'Total Salidas'  => array_sum($salidas),
            ],
        ]);
    }

    // ── Estadísticas específicas por tipo ─────────────────────────────────────

    public function estadisticas(Request $r)
    {
        $this->validarFechas($r);
        $tipo  = $r->input('tipo', '');
        $desde = $r->desde;
        $hasta = $r->hasta;

        $secciones = match ($tipo) {
            'pedidos'           => $this->statsPedidos($desde, $hasta),
            'presupuestos'      => $this->statsPresupuestos($desde, $hasta),
            'ordenes_compras'   => $this->statsOrdenesCompras($desde, $hasta),
            'compras'           => $this->statsCompras($desde, $hasta),
            'libro_compras'     => $this->statsLibroCompras($desde, $hasta),
            'nota_remi_comp'    => $this->statsNotaRemiComp($desde, $hasta),
            'ajuste_inventario' => $this->statsAjusteInventario($desde, $hasta),
            'notas_compra'      => $this->statsNotasCompra($desde, $hasta),
            default             => [],
        };

        $tieneFiltros = collect($secciones)->contains(fn($s) => !empty($s['filtros']));
        $catalogos = [];
        if ($tieneFiltros) {
            $catalogos = [
                'sucursales'     => DB::select("SELECT id, suc_razon_social AS nombre FROM sucursal ORDER BY suc_razon_social"),
                'depositos'      => DB::select("SELECT id, dep_nombre AS nombre, sucursal_id FROM deposito ORDER BY dep_nombre"),
                'proveedores'    => DB::select("SELECT id, prov_razonsocial AS nombre FROM proveedores ORDER BY prov_razonsocial"),
                'tipos_impuesto'  => $tipo === 'libro_compras'
                    ? DB::select("SELECT DISTINCT tip_imp_nom AS id, tip_imp_nom AS nombre FROM libro_compras WHERE tip_imp_nom IS NOT NULL ORDER BY tip_imp_nom")
                    : [],
                'tipos_nota_remi' => $tipo === 'nota_remi_comp'
                    ? DB::select("SELECT DISTINCT tipo AS id, tipo AS nombre FROM nota_remi_comp WHERE tipo IS NOT NULL ORDER BY tipo")
                    : [],
                'tipos_ajuste'    => $tipo === 'ajuste_inventario'
                    ? DB::select("SELECT DISTINCT tipo_ajuste AS id, tipo_ajuste AS nombre FROM ajuste_cab WHERE tipo_ajuste IS NOT NULL ORDER BY tipo_ajuste")
                    : [],
                'tipos_nota_comp' => $tipo === 'notas_compra'
                    ? DB::select("SELECT DISTINCT nota_comp_tipo AS id, nota_comp_tipo AS nombre FROM notas_comp_cab WHERE nota_comp_tipo IS NOT NULL ORDER BY nota_comp_tipo")
                    : [],
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
        $sucId   = $r->filled('sucursal_id')  ? (int)$r->sucursal_id  : null;
        $depId   = $r->filled('deposito_id')  ? (int)$r->deposito_id  : null;
        $provId  = $r->filled('proveedor_id') ? (int)$r->proveedor_id : null;

        $result = match ($tipo . '::' . $seccion) {
            'pedidos::ped_sucursal'        => $this->pedidosPorSucursal($desde, $hasta, $sucId, $depId),
            'pedidos::ped_mes'             => $this->pedidosPorMes($desde, $hasta, $sucId),
            'pedidos::ped_items'           => $this->pedidosItemsMasPedidos($desde, $hasta, $sucId, $depId),
            'presupuestos::pre_proveedor'  => $this->presupuestosTopProveedores($desde, $hasta, $sucId),
            'presupuestos::pre_mes'        => $this->presupuestosStatsMes($desde, $hasta, $sucId, $provId),
            'presupuestos::pre_items'      => $this->presupuestosItemsMasPresupuestados($desde, $hasta, $sucId, $provId),
            'ordenes_compras::oc_condicion' => $this->ocPorCondicion($desde, $hasta, $sucId),
            'ordenes_compras::oc_mes'       => $this->ocStatsMes($desde, $hasta, $sucId, $provId),
            'ordenes_compras::oc_proveedor' => $this->ocTopProveedores($desde, $hasta, $sucId),
            'ordenes_compras::oc_items'     => $this->ocItemsMasOrdenados($desde, $hasta, $sucId, $depId),
            'compras::comp_proveedor'       => $this->compTopProveedores($desde, $hasta, $sucId),
            'compras::comp_ctas_pagar'      => $this->compCtasPagar($desde, $hasta, $provId),
            'compras::comp_mes'             => $this->compPorMes($desde, $hasta, $sucId, $provId),
            'compras::comp_items'           => $this->compTopItems($desde, $hasta, $sucId, $depId),
            'libro_compras::lc_mes'           => $this->lcPorMes($desde, $hasta, $r->input('tipo_impuesto') ?: null),
            'libro_compras::lc_proveedor'     => $this->lcTopProveedores($desde, $hasta, $r->input('tipo_impuesto') ?: null),
            'nota_remi_comp::nrc_sucursal'      => $this->nrcPorSucursal($desde, $hasta, $r->input('tipo_nrc') ?: null),
            'nota_remi_comp::nrc_mes'          => $this->nrcPorMes($desde, $hasta, $sucId, $r->input('tipo_nrc') ?: null),
            'nota_remi_comp::nrc_items'        => $this->nrcTopItems($desde, $hasta, $sucId, $depId),
            'ajuste_inventario::aj_motivo'     => $this->ajPorMotivo($desde, $hasta, $sucId, $r->input('tipo_ajuste') ?: null),
            'ajuste_inventario::aj_mes'        => $this->ajPorMes($desde, $hasta, $sucId),
            'ajuste_inventario::aj_items'      => $this->ajTopItems($desde, $hasta, $sucId, $depId),
            'notas_compra::nc_mes'             => $this->ncPorMes($desde, $hasta, $sucId, $r->input('tipo_nc') ?: null),
            'notas_compra::nc_proveedor'       => $this->ncTopProveedores($desde, $hasta, $sucId),
            'notas_compra::nc_items'           => $this->ncTopItems($desde, $hasta, $sucId, $depId),
            default                            => null,
        };

        if (!$result) {
            return response()->json(['error' => 'Sección no encontrada'], 404);
        }

        return response()->json(['seccion' => $result]);
    }

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

    private function graficoPorMes(string $sql, array $params, string $titulo, string $colMes, string $colValor, string $labelValor, string $color): array
    {
        $data = DB::select($sql, $params);
        return [
            'id'           => 'mes_' . md5($titulo),
            'titulo'       => $titulo,
            'tipo_grafico' => 'line',
            'labels'       => array_map(fn($r) => $r->{$colMes}, $data),
            'datasets'     => [['label' => $labelValor, 'data' => array_map(fn($r) => (int)$r->{$colValor}, $data), 'color' => $color]],
            'columnas'     => [['key' => $colMes, 'label' => 'Mes'], ['key' => $colValor, 'label' => $labelValor]],
            'tabla'        => $data,
        ];
    }

    private function statsPedidos(string $desde, string $hasta): array
    {
        return [
            $this->graficoEstados('pedidos', 'ped_fecha', 'ped_estado', 'Pedidos por Estado', '#2980b9', $desde, $hasta),
            $this->pedidosPorSucursal($desde, $hasta),
            $this->pedidosPorMes($desde, $hasta),
            $this->pedidosItemsMasPedidos($desde, $hasta),
        ];
    }

    private function pedidosPorSucursal(string $desde, string $hasta, ?int $sucursalId = null, ?int $depositoId = null): array
    {
        // Consulta para el gráfico: pedidos agrupados por sucursal
        $chartWhere  = "WHERE p.ped_fecha BETWEEN :desde AND :hasta";
        $chartParams = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $chartWhere .= " AND p.sucursal_id = :suc_c";
            $chartParams['suc_c'] = $sucursalId;
        }
        $chartJoin = $depositoId
            ? "JOIN pedidos_detalles pd_f ON pd_f.pedidos_id = p.id AND pd_f.deposito_id = :dep_c"
            : '';
        if ($depositoId) $chartParams['dep_c'] = $depositoId;

        $chartData = DB::select("
            SELECT s.suc_razon_social AS sucursal, COUNT(DISTINCT p.id) AS cantidad
            FROM pedidos p
            JOIN sucursal s ON s.id = p.sucursal_id
            {$chartJoin}
            {$chartWhere}
            GROUP BY s.suc_razon_social ORDER BY cantidad DESC
        ", $chartParams);

        // Consulta para la tabla: desglose por sucursal + depósito
        $tableWhere  = "WHERE p.ped_fecha BETWEEN :desde AND :hasta";
        $tableParams = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $tableWhere .= " AND p.sucursal_id = :sucursal_id";
            $tableParams['sucursal_id'] = $sucursalId;
        }
        if ($depositoId) {
            $tableWhere .= " AND pd.deposito_id = :deposito_id";
            $tableParams['deposito_id'] = $depositoId;
        }

        $tableData = DB::select("
            SELECT s.suc_razon_social AS sucursal,
                   COALESCE(d.dep_nombre, 'Sin depósito') AS deposito,
                   COUNT(DISTINCT p.id) AS cantidad
            FROM pedidos p
            JOIN sucursal s ON s.id = p.sucursal_id
            LEFT JOIN pedidos_detalles pd ON pd.pedidos_id = p.id
            LEFT JOIN deposito d ON d.id = pd.deposito_id
            {$tableWhere}
            GROUP BY s.suc_razon_social, d.dep_nombre
            ORDER BY s.suc_razon_social, cantidad DESC
        ", $tableParams);

        return [
            'id'           => 'ped_sucursal',
            'titulo'       => 'Pedidos por Sucursal',
            'tipo_grafico' => 'bar',
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
                ['id' => 'deposito', 'label' => 'Depósito', 'cascade' => 'sucursal'],
            ],
            'labels'   => array_map(fn($r) => $r->sucursal, $chartData),
            'datasets' => [['label' => 'Pedidos', 'data' => array_map(fn($r) => (int)$r->cantidad, $chartData), 'color' => '#27ae60']],
            'columnas' => [
                ['key' => 'sucursal', 'label' => 'Sucursal'],
                ['key' => 'deposito', 'label' => 'Depósito'],
                ['key' => 'cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $tableData,
        ];
    }

    private function pedidosPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE p.ped_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND p.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', p.ped_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM pedidos p
            {$where}
            GROUP BY DATE_TRUNC('month', p.ped_fecha)
            ORDER BY DATE_TRUNC('month', p.ped_fecha)
        ", $params);

        return [
            'id'           => 'ped_mes',
            'titulo'       => 'Pedidos por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
            ],
            'labels'   => array_map(fn($r) => $r->mes, $data),
            'datasets' => [['label' => 'Pedidos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#8e44ad']],
            'columnas' => [
                ['key' => 'mes',      'label' => 'Mes'],
                ['key' => 'cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function pedidosItemsMasPedidos(string $desde, string $hasta, ?int $sucursalId = null, ?int $depositoId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE p.ped_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND p.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($depositoId) {
            $where .= " AND pd.deposito_id = :deposito_id";
            $params['deposito_id'] = $depositoId;
        }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   s.suc_razon_social AS sucursal,
                   COALESCE(d.dep_nombre, 'Sin depósito') AS deposito,
                   SUM(pd.det_cantidad) AS total_cantidad
            FROM pedidos_detalles pd
            JOIN pedidos  p ON p.id = pd.pedidos_id
            JOIN items    i ON i.id = pd.item_id
            JOIN sucursal s ON s.id = p.sucursal_id
            LEFT JOIN deposito d ON d.id = pd.deposito_id
            {$where}
            GROUP BY i.item_decripcion, s.suc_razon_social, d.dep_nombre
            ORDER BY total_cantidad DESC
            LIMIT {$topN}
        ", $params);

        // Pivote por ítem para el gráfico (suma entre sucursales/depósitos)
        $pivot = [];
        foreach ($data as $row) {
            $pivot[$row->item] = ($pivot[$row->item] ?? 0) + (float)$row->total_cantidad;
        }
        arsort($pivot);
        $pivot = array_slice($pivot, 0, $topN, true);

        return [
            'id'           => 'ped_items',
            'titulo'       => 'Top ' . $topN . ' Ítems Más Pedidos',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
                ['id' => 'deposito', 'label' => 'Depósito', 'cascade' => 'sucursal'],
            ],
            'labels'   => array_keys($pivot),
            'datasets' => [['label' => 'Cantidad Total', 'data' => array_values($pivot), 'color' => '#e74c3c']],
            'columnas' => [
                ['key' => 'item',           'label' => 'Ítem'],
                ['key' => 'sucursal',       'label' => 'Sucursal'],
                ['key' => 'deposito',       'label' => 'Depósito'],
                ['key' => 'total_cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function statsPresupuestos(string $desde, string $hasta): array
    {
        return [
            $this->graficoEstados('presupuestos', 'pre_fecha', 'pre_estado', 'Presupuestos por Estado', '#2980b9', $desde, $hasta),
            $this->presupuestosTopProveedores($desde, $hasta),
            $this->presupuestosStatsMes($desde, $hasta),
            $this->presupuestosItemsMasPresupuestados($desde, $hasta),
        ];
    }

    private function presupuestosTopProveedores(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $where  = "WHERE p.pre_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND p.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        $data = DB::select("
            SELECT prov.prov_razonsocial AS proveedor,
                   COUNT(DISTINCT p.id) AS cantidad,
                   COALESCE(SUM(pd.det_cantidad * pd.det_costo), 0) AS monto_total
            FROM presupuestos p
            JOIN proveedores prov ON prov.id = p.proveedor_id
            LEFT JOIN presupuestos_detalles pd ON pd.presupuesto_id = p.id
            {$where}
            GROUP BY prov.prov_razonsocial
            ORDER BY monto_total DESC
            LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'pre_proveedor',
            'titulo'       => 'Top ' . $topN . ' Proveedores por Monto',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
            ],
            'labels'   => array_map(fn($r) => $r->proveedor, $data),
            'datasets' => [['label' => 'Monto (Gs.)', 'data' => array_map(fn($r) => (float)$r->monto_total, $data), 'color' => '#f39c12']],
            'columnas' => [
                ['key' => 'proveedor',   'label' => 'Proveedor'],
                ['key' => 'cantidad',    'label' => 'Presupuestos'],
                ['key' => 'monto_total', 'label' => 'Monto Total (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function presupuestosStatsMes(string $desde, string $hasta, ?int $sucursalId = null, ?int $proveedorId = null): array
    {
        $where  = "WHERE p.pre_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND p.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($proveedorId) {
            $where .= " AND p.proveedor_id = :proveedor_id";
            $params['proveedor_id'] = $proveedorId;
        }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', p.pre_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM presupuestos p
            {$where}
            GROUP BY DATE_TRUNC('month', p.pre_fecha)
            ORDER BY DATE_TRUNC('month', p.pre_fecha)
        ", $params);

        return [
            'id'           => 'pre_mes',
            'titulo'       => 'Presupuestos por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [
                ['id' => 'sucursal',  'label' => 'Sucursal'],
                ['id' => 'proveedor', 'label' => 'Proveedor'],
            ],
            'labels'   => array_map(fn($r) => $r->mes, $data),
            'datasets' => [['label' => 'Presupuestos', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#16a085']],
            'columnas' => [
                ['key' => 'mes',      'label' => 'Mes'],
                ['key' => 'cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function presupuestosItemsMasPresupuestados(string $desde, string $hasta, ?int $sucursalId = null, ?int $proveedorId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE p.pre_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND p.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($proveedorId) {
            $where .= " AND p.proveedor_id = :proveedor_id";
            $params['proveedor_id'] = $proveedorId;
        }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   SUM(pd.det_cantidad) AS total_cantidad,
                   COALESCE(SUM(pd.det_cantidad * pd.det_costo), 0) AS total_monto
            FROM presupuestos_detalles pd
            JOIN presupuestos p ON p.id = pd.presupuesto_id
            JOIN items        i ON i.id = pd.item_id
            {$where}
            GROUP BY i.item_decripcion
            ORDER BY total_cantidad DESC
            LIMIT {$topN}
        ", $params);

        $pivot = [];
        foreach ($data as $row) {
            $pivot[$row->item] = ($pivot[$row->item] ?? 0) + (float)$row->total_cantidad;
        }
        arsort($pivot);
        $pivot = array_slice($pivot, 0, $topN, true);

        return [
            'id'           => 'pre_items',
            'titulo'       => 'Top ' . $topN . ' Ítems Más Presupuestados',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [
                ['id' => 'sucursal',  'label' => 'Sucursal'],
                ['id' => 'proveedor', 'label' => 'Proveedor'],
            ],
            'labels'   => array_keys($pivot),
            'datasets' => [['label' => 'Cantidad', 'data' => array_values($pivot), 'color' => '#2980b9']],
            'columnas' => [
                ['key' => 'item',           'label' => 'Ítem'],
                ['key' => 'total_cantidad', 'label' => 'Cantidad'],
                ['key' => 'total_monto',    'label' => 'Monto Total (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function statsOrdenesCompras(string $desde, string $hasta): array
    {
        return [
            $this->graficoEstados('orden_compra_cab', 'ord_comp_fecha', 'ord_comp_estado', 'Órdenes de Compra por Estado', '#2980b9', $desde, $hasta),
            $this->ocPorCondicion($desde, $hasta),
            $this->ocStatsMes($desde, $hasta),
            $this->ocTopProveedores($desde, $hasta),
            $this->ocItemsMasOrdenados($desde, $hasta),
        ];
    }

    private function ocPorCondicion(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE ord_comp_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        $data = DB::select("
            SELECT COALESCE(condicion_pago, 'Sin especificar') AS condicion,
                   COUNT(*) AS cantidad
            FROM orden_compra_cab
            {$where}
            GROUP BY condicion_pago ORDER BY cantidad DESC
        ", $params);

        return [
            'id'           => 'oc_condicion',
            'titulo'       => 'OC por Condición de Pago',
            'tipo_grafico' => 'bar',
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
            ],
            'labels'   => array_map(fn($r) => $r->condicion, $data),
            'datasets' => [['label' => 'OC', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#e74c3c']],
            'columnas' => [
                ['key' => 'condicion', 'label' => 'Condición de Pago'],
                ['key' => 'cantidad',  'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function ocStatsMes(string $desde, string $hasta, ?int $sucursalId = null, ?int $proveedorId = null): array
    {
        $where  = "WHERE occ.ord_comp_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND occ.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($proveedorId) {
            $where .= " AND occ.proveedor_id = :proveedor_id";
            $params['proveedor_id'] = $proveedorId;
        }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', occ.ord_comp_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM orden_compra_cab occ
            {$where}
            GROUP BY DATE_TRUNC('month', occ.ord_comp_fecha)
            ORDER BY DATE_TRUNC('month', occ.ord_comp_fecha)
        ", $params);

        return [
            'id'           => 'oc_mes',
            'titulo'       => 'Órdenes de Compra por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [
                ['id' => 'sucursal',  'label' => 'Sucursal'],
                ['id' => 'proveedor', 'label' => 'Proveedor'],
            ],
            'labels'   => array_map(fn($r) => $r->mes, $data),
            'datasets' => [['label' => 'OC', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#8e44ad']],
            'columnas' => [
                ['key' => 'mes',      'label' => 'Mes'],
                ['key' => 'cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function ocTopProveedores(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $where  = "WHERE occ.ord_comp_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND occ.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        $data = DB::select("
            SELECT prov.prov_razonsocial AS proveedor,
                   COUNT(DISTINCT occ.id) AS cantidad
            FROM orden_compra_cab occ
            JOIN proveedores prov ON prov.id = occ.proveedor_id
            {$where}
            GROUP BY prov.prov_razonsocial
            ORDER BY cantidad DESC
            LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'oc_proveedor',
            'titulo'       => 'Top ' . $topN . ' Proveedores por OC',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
            ],
            'labels'   => array_map(fn($r) => $r->proveedor, $data),
            'datasets' => [['label' => 'Órdenes', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#27ae60']],
            'columnas' => [
                ['key' => 'proveedor', 'label' => 'Proveedor'],
                ['key' => 'cantidad',  'label' => 'Cantidad de OC'],
            ],
            'tabla' => $data,
        ];
    }

    private function ocItemsMasOrdenados(string $desde, string $hasta, ?int $sucursalId = null, ?int $depositoId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE occ.ord_comp_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND occ.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($depositoId) {
            $where .= " AND ocd.deposito_id = :deposito_id";
            $params['deposito_id'] = $depositoId;
        }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   SUM(ocd.orden_compra_det_cantidad) AS total_cantidad,
                   s.suc_razon_social AS sucursal,
                   COALESCE(d.dep_nombre, 'Sin depósito') AS deposito
            FROM orden_compra_det ocd
            JOIN orden_compra_cab occ ON occ.id = ocd.orden_compra_cab_id
            JOIN items    i ON i.id  = ocd.item_id
            JOIN sucursal s ON s.id  = occ.sucursal_id
            LEFT JOIN deposito d ON d.id = ocd.deposito_id
            {$where}
            GROUP BY i.item_decripcion, s.suc_razon_social, d.dep_nombre
            ORDER BY total_cantidad DESC
            LIMIT {$topN}
        ", $params);

        $pivot = [];
        foreach ($data as $row) {
            $pivot[$row->item] = ($pivot[$row->item] ?? 0) + (float)$row->total_cantidad;
        }
        arsort($pivot);
        $pivot = array_slice($pivot, 0, $topN, true);

        return [
            'id'           => 'oc_items',
            'titulo'       => 'Top ' . $topN . ' Ítems Más Ordenados',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
                ['id' => 'deposito', 'label' => 'Depósito', 'cascade' => 'sucursal'],
            ],
            'labels'   => array_keys($pivot),
            'datasets' => [['label' => 'Cantidad Total', 'data' => array_values($pivot), 'color' => '#d35400']],
            'columnas' => [
                ['key' => 'item',           'label' => 'Ítem'],
                ['key' => 'sucursal',       'label' => 'Sucursal'],
                ['key' => 'deposito',       'label' => 'Depósito'],
                ['key' => 'total_cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function statsCompras(string $desde, string $hasta): array
    {
        return [
            $this->graficoEstados('compra_cab', 'comp_fecha', 'comp_estado', 'Compras por Estado', '#2980b9', $desde, $hasta),
            $this->compTopProveedores($desde, $hasta),
            $this->compCtasPagar($desde, $hasta),
            $this->compPorMes($desde, $hasta),
            $this->compTopItems($desde, $hasta),
        ];
    }

    private function compTopProveedores(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $where  = "WHERE cc.comp_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND cc.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        $data = DB::select("
            SELECT COALESCE(prov.prov_razonsocial, 'Sin proveedor') AS proveedor,
                   COUNT(DISTINCT cc.id) AS cantidad
            FROM compra_cab cc
            LEFT JOIN proveedores prov ON prov.id = cc.proveedor_id
            {$where}
            GROUP BY prov.prov_razonsocial
            ORDER BY cantidad DESC
            LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'comp_proveedor',
            'titulo'       => 'Top ' . $topN . ' Proveedores por Compras',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
            ],
            'labels'   => array_map(fn($r) => $r->proveedor, $data),
            'datasets' => [['label' => 'Compras', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#27ae60']],
            'columnas' => [
                ['key' => 'proveedor', 'label' => 'Proveedor'],
                ['key' => 'cantidad',  'label' => 'Cantidad de Compras'],
            ],
            'tabla' => $data,
        ];
    }

    private function compCtasPagar(string $desde, string $hasta, ?int $proveedorId = null): array
    {
        $where  = "WHERE cp.cta_pag_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($proveedorId) {
            $where .= " AND cc.proveedor_id = :proveedor_id";
            $params['proveedor_id'] = $proveedorId;
        }

        $data = DB::select("
            SELECT cp.cta_pag_estado AS estado,
                   COUNT(*) AS cantidad,
                   SUM(cp.cta_pag_monto) AS monto_total
            FROM ctas_pagar cp
            JOIN compra_cab cc ON cc.id = cp.compra_cab_id
            {$where}
            GROUP BY cp.cta_pag_estado ORDER BY cantidad DESC
        ", $params);

        return [
            'id'           => 'comp_ctas_pagar',
            'titulo'       => 'Cuentas a Pagar por Estado',
            'tipo_grafico' => 'bar',
            'filtros'      => [
                ['id' => 'proveedor', 'label' => 'Proveedor'],
            ],
            'labels'   => array_map(fn($r) => $r->estado, $data),
            'datasets' => [['label' => 'Monto (Gs.)', 'data' => array_map(fn($r) => (float)$r->monto_total, $data), 'color' => '#e74c3c']],
            'columnas' => [
                ['key' => 'estado',      'label' => 'Estado'],
                ['key' => 'cantidad',    'label' => 'Cuotas'],
                ['key' => 'monto_total', 'label' => 'Monto (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function compPorMes(string $desde, string $hasta, ?int $sucursalId = null, ?int $proveedorId = null): array
    {
        $where  = "WHERE cc.comp_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND cc.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($proveedorId) {
            $where .= " AND cc.proveedor_id = :proveedor_id";
            $params['proveedor_id'] = $proveedorId;
        }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', cc.comp_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM compra_cab cc
            {$where}
            GROUP BY DATE_TRUNC('month', cc.comp_fecha)
            ORDER BY DATE_TRUNC('month', cc.comp_fecha)
        ", $params);

        return [
            'id'           => 'comp_mes',
            'titulo'       => 'Compras por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [
                ['id' => 'sucursal',  'label' => 'Sucursal'],
                ['id' => 'proveedor', 'label' => 'Proveedor'],
            ],
            'labels'   => array_map(fn($r) => $r->mes, $data),
            'datasets' => [['label' => 'Compras', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#2980b9']],
            'columnas' => [
                ['key' => 'mes',      'label' => 'Mes'],
                ['key' => 'cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function compTopItems(string $desde, string $hasta, ?int $sucursalId = null, ?int $depositoId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE cc.comp_fecha BETWEEN :desde AND :hasta AND cc.comp_estado NOT IN ('ANULADO')";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND cc.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($depositoId) {
            $where .= " AND cd.deposito_id = :deposito_id";
            $params['deposito_id'] = $depositoId;
        }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   SUM(cd.comp_det_cantidad) AS total_cantidad,
                   COALESCE(SUM(cd.comp_det_cantidad * cd.comp_det_costo), 0) AS total_monto,
                   s.suc_razon_social AS sucursal,
                   COALESCE(d.dep_nombre, 'Sin depósito') AS deposito
            FROM compra_det cd
            JOIN compra_cab cc ON cc.id = cd.compra_cab_id
            JOIN items      i  ON i.id  = cd.item_id
            JOIN sucursal   s  ON s.id  = cc.sucursal_id
            LEFT JOIN deposito d ON d.id = cd.deposito_id
            {$where}
            GROUP BY i.item_decripcion, s.suc_razon_social, d.dep_nombre
            ORDER BY total_cantidad DESC
            LIMIT {$topN}
        ", $params);

        $pivot = [];
        foreach ($data as $row) {
            $pivot[$row->item] = ($pivot[$row->item] ?? 0) + (float)$row->total_cantidad;
        }
        arsort($pivot);
        $pivot = array_slice($pivot, 0, $topN, true);

        return [
            'id'           => 'comp_items',
            'titulo'       => 'Top ' . $topN . ' Ítems Más Comprados',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
                ['id' => 'deposito', 'label' => 'Depósito', 'cascade' => 'sucursal'],
            ],
            'labels'   => array_keys($pivot),
            'datasets' => [['label' => 'Cantidad', 'data' => array_values($pivot), 'color' => '#16a085']],
            'columnas' => [
                ['key' => 'item',           'label' => 'Ítem'],
                ['key' => 'sucursal',       'label' => 'Sucursal'],
                ['key' => 'deposito',       'label' => 'Depósito'],
                ['key' => 'total_cantidad', 'label' => 'Cantidad'],
                ['key' => 'total_monto',    'label' => 'Monto Total (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function statsLibroCompras(string $desde, string $hasta): array
    {
        return [
            $this->lcPorImpuesto($desde, $hasta),
            $this->lcPorMes($desde, $hasta),
            $this->lcTopProveedores($desde, $hasta),
        ];
    }

    private function lcPorImpuesto(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(tip_imp_nom, 'Sin tipo') AS tipo,
                   COUNT(*) AS cantidad,
                   COALESCE(SUM(\"libC_monto\"), 0) AS monto
            FROM libro_compras
            WHERE \"libC_fecha\" BETWEEN :desde AND :hasta
            GROUP BY tip_imp_nom ORDER BY monto DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'lc_impuesto',
            'titulo'       => 'Distribución por Tipo de Impuesto',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->tipo, $data),
            'datasets'     => [['label' => 'Monto (Gs.)', 'data' => array_map(fn($r) => (float)$r->monto, $data)]],
            'columnas'     => [
                ['key' => 'tipo',     'label' => 'Tipo de Impuesto'],
                ['key' => 'cantidad', 'label' => 'Registros'],
                ['key' => 'monto',    'label' => 'Monto (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function lcPorMes(string $desde, string $hasta, ?string $tipoImpuesto = null): array
    {
        $where  = "WHERE \"libC_fecha\" BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($tipoImpuesto) {
            $where .= " AND tip_imp_nom = :tipo_impuesto";
            $params['tipo_impuesto'] = $tipoImpuesto;
        }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', \"libC_fecha\"), 'MM/YYYY') AS mes,
                   COALESCE(SUM(\"libC_monto\"), 0) AS monto
            FROM libro_compras
            {$where}
            GROUP BY DATE_TRUNC('month', \"libC_fecha\")
            ORDER BY DATE_TRUNC('month', \"libC_fecha\")
        ", $params);

        return [
            'id'           => 'lc_mes',
            'titulo'       => 'Monto por Mes — Libro de Compras',
            'tipo_grafico' => 'line',
            'filtros'      => [
                ['id' => 'tipo_impuesto', 'label' => 'Tipo de Impuesto'],
            ],
            'labels'   => array_map(fn($r) => $r->mes, $data),
            'datasets' => [['label' => 'Monto (Gs.)', 'data' => array_map(fn($r) => (float)$r->monto, $data), 'color' => '#2980b9']],
            'columnas' => [
                ['key' => 'mes',   'label' => 'Mes'],
                ['key' => 'monto', 'label' => 'Monto (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function lcTopProveedores(string $desde, string $hasta, ?string $tipoImpuesto = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE \"libC_fecha\" BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($tipoImpuesto) {
            $where .= " AND tip_imp_nom = :tipo_impuesto";
            $params['tipo_impuesto'] = $tipoImpuesto;
        }

        $data = DB::select("
            SELECT COALESCE(prov_razonsocial, 'Sin proveedor') AS proveedor,
                   COALESCE(SUM(\"libC_monto\"), 0) AS monto
            FROM libro_compras
            {$where}
            GROUP BY prov_razonsocial
            ORDER BY monto DESC
            LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'lc_proveedor',
            'titulo'       => 'Top ' . $topN . ' Proveedores — Libro de Compras',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [
                ['id' => 'tipo_impuesto', 'label' => 'Tipo de Impuesto'],
            ],
            'labels'   => array_map(fn($r) => $r->proveedor, $data),
            'datasets' => [['label' => 'Monto (Gs.)', 'data' => array_map(fn($r) => (float)$r->monto, $data), 'color' => '#f39c12']],
            'columnas' => [
                ['key' => 'proveedor', 'label' => 'Proveedor'],
                ['key' => 'monto',     'label' => 'Monto (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function statsNotaRemiComp(string $desde, string $hasta): array
    {
        return [
            $this->nrcPorTipo($desde, $hasta),
            $this->graficoEstados('nota_remi_comp', 'nota_remi_fecha', 'nota_remi_estado', 'Notas de Remisión por Estado', '#2980b9', $desde, $hasta),
            $this->nrcPorSucursal($desde, $hasta),
            $this->nrcPorMes($desde, $hasta),
            $this->nrcTopItems($desde, $hasta),
        ];
    }

    private function nrcPorTipo(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(tipo, 'Sin tipo') AS tipo,
                   COUNT(*) AS cantidad
            FROM nota_remi_comp
            WHERE nota_remi_fecha BETWEEN :desde AND :hasta
            GROUP BY tipo ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'nrc_tipo',
            'titulo'       => 'Notas de Remisión por Tipo',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->tipo, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [
                ['key' => 'tipo',     'label' => 'Tipo'],
                ['key' => 'cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function nrcPorSucursal(string $desde, string $hasta, ?string $tipoNrc = null): array
    {
        $where  = "WHERE nrc.nota_remi_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($tipoNrc) {
            $where .= " AND nrc.tipo = :tipo";
            $params['tipo'] = $tipoNrc;
        }

        $data = DB::select("
            SELECT s.suc_razon_social AS sucursal,
                   COUNT(*) AS cantidad
            FROM nota_remi_comp nrc
            JOIN sucursal s ON s.id = nrc.sucursal_id
            {$where}
            GROUP BY s.suc_razon_social ORDER BY cantidad DESC
        ", $params);

        return [
            'id'           => 'nrc_sucursal',
            'titulo'       => 'Notas de Remisión por Sucursal',
            'tipo_grafico' => 'bar',
            'filtros'      => [
                ['id' => 'tipo_nrc', 'label' => 'Tipo'],
            ],
            'labels'   => array_map(fn($r) => $r->sucursal, $data),
            'datasets' => [['label' => 'Notas', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#27ae60']],
            'columnas' => [
                ['key' => 'sucursal', 'label' => 'Sucursal'],
                ['key' => 'cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function nrcPorMes(string $desde, string $hasta, ?int $sucursalId = null, ?string $tipoNrc = null): array
    {
        $where  = "WHERE nota_remi_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($tipoNrc) {
            $where .= " AND tipo = :tipo";
            $params['tipo'] = $tipoNrc;
        }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', nota_remi_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM nota_remi_comp
            {$where}
            GROUP BY DATE_TRUNC('month', nota_remi_fecha)
            ORDER BY DATE_TRUNC('month', nota_remi_fecha)
        ", $params);

        return [
            'id'           => 'nrc_mes',
            'titulo'       => 'Notas de Remisión por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
                ['id' => 'tipo_nrc', 'label' => 'Tipo'],
            ],
            'labels'   => array_map(fn($r) => $r->mes, $data),
            'datasets' => [['label' => 'Notas', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#8e44ad']],
            'columnas' => [
                ['key' => 'mes',      'label' => 'Mes'],
                ['key' => 'cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function nrcTopItems(string $desde, string $hasta, ?int $sucursalId = null, ?int $depositoId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE nrc.nota_remi_fecha BETWEEN :desde AND :hasta AND nrc.nota_remi_estado = 'CONFIRMADO'";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND nrc.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($depositoId) {
            $where .= " AND nrd.deposito_id = :deposito_id";
            $params['deposito_id'] = $depositoId;
        }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   COALESCE(d_orig.dep_nombre, 'Sin origen')  AS deposito_origen,
                   COALESCE(d_dest.dep_nombre, 'Sin destino') AS deposito_destino,
                   SUM(nrd.nota_remi_com_det_cantidad) AS total_cantidad
            FROM nota_remi_com_det nrd
            JOIN nota_remi_comp nrc ON nrc.id  = nrd.nota_remi_comp_id
            JOIN items          i   ON i.id    = nrd.item_id
            LEFT JOIN deposito d_orig ON d_orig.id = nrd.deposito_id
            LEFT JOIN deposito d_dest ON d_dest.id = nrd.deposito_destino_id
            {$where}
            GROUP BY i.item_decripcion, d_orig.dep_nombre, d_dest.dep_nombre
            ORDER BY total_cantidad DESC
            LIMIT {$topN}
        ", $params);

        $pivot = [];
        foreach ($data as $row) {
            $pivot[$row->item] = ($pivot[$row->item] ?? 0) + (float)$row->total_cantidad;
        }
        arsort($pivot);
        $pivot = array_slice($pivot, 0, $topN, true);

        return [
            'id'           => 'nrc_items',
            'titulo'       => 'Top ' . $topN . ' Ítems Más Transferidos',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
                ['id' => 'deposito', 'label' => 'Depósito Origen', 'cascade' => 'sucursal'],
            ],
            'labels'   => array_keys($pivot),
            'datasets' => [['label' => 'Cantidad Transferida', 'data' => array_values($pivot), 'color' => '#e74c3c']],
            'columnas' => [
                ['key' => 'item',             'label' => 'Ítem'],
                ['key' => 'deposito_origen',  'label' => 'Dep. Origen'],
                ['key' => 'deposito_destino', 'label' => 'Dep. Destino'],
                ['key' => 'total_cantidad',   'label' => 'Cant. Transferida'],
            ],
            'tabla' => $data,
        ];
    }

    private function statsAjusteInventario(string $desde, string $hasta): array
    {
        return [
            $this->ajPorTipo($desde, $hasta),
            $this->graficoEstados('ajuste_cab', 'ajus_cab_fecha', 'ajus_cab_estado', 'Ajustes por Estado', '#2980b9', $desde, $hasta),
            $this->ajPorMotivo($desde, $hasta),
            $this->ajPorMes($desde, $hasta),
            $this->ajTopItems($desde, $hasta),
        ];
    }

    private function ajPorTipo(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(tipo_ajuste, 'Sin tipo') AS tipo,
                   COUNT(*) AS cantidad
            FROM ajuste_cab
            WHERE ajus_cab_fecha BETWEEN :desde AND :hasta
            GROUP BY tipo_ajuste ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'aj_tipo',
            'titulo'       => 'Ajustes por Tipo (Entrada / Salida)',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->tipo, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [
                ['key' => 'tipo',     'label' => 'Tipo'],
                ['key' => 'cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function ajPorMotivo(string $desde, string $hasta, ?int $sucursalId = null, ?string $tipoAjuste = null): array
    {
        $where  = "WHERE ac.ajus_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND ac.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($tipoAjuste) {
            $where .= " AND ac.tipo_ajuste = :tipo_ajuste";
            $params['tipo_ajuste'] = $tipoAjuste;
        }

        $data = DB::select("
            SELECT ma.descripcion AS motivo,
                   ac.tipo_ajuste,
                   COUNT(*) AS cantidad
            FROM ajuste_cab ac
            JOIN motivo_ajuste ma ON ma.id = ac.motivo_ajuste_id
            {$where}
            GROUP BY ma.descripcion, ac.tipo_ajuste
            ORDER BY ma.descripcion, ac.tipo_ajuste
        ", $params);

        $allMotivos = array_values(array_unique(array_column((array)json_decode(json_encode($data)), 'motivo')));
        $entradas   = [];
        $salidas    = [];
        foreach ($allMotivos as $m) {
            $e = collect($data)->first(fn($r) => $r->motivo === $m && $r->tipo_ajuste === 'Entrada');
            $s = collect($data)->first(fn($r) => $r->motivo === $m && $r->tipo_ajuste === 'Salida');
            $entradas[] = $e ? (int)$e->cantidad : 0;
            $salidas[]  = $s ? (int)$s->cantidad : 0;
        }

        return [
            'id'           => 'aj_motivo',
            'titulo'       => 'Ajustes por Motivo (Entrada / Salida)',
            'tipo_grafico' => 'bar',
            'filtros'      => [
                ['id' => 'sucursal',    'label' => 'Sucursal'],
                ['id' => 'tipo_ajuste', 'label' => 'Tipo'],
            ],
            'labels'   => $allMotivos,
            'datasets' => [
                ['label' => 'Entrada', 'data' => $entradas, 'color' => '#27ae60'],
                ['label' => 'Salida',  'data' => $salidas,  'color' => '#e74c3c'],
            ],
            'columnas' => [
                ['key' => 'motivo',      'label' => 'Motivo'],
                ['key' => 'tipo_ajuste', 'label' => 'Tipo'],
                ['key' => 'cantidad',    'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function ajPorMes(string $desde, string $hasta, ?int $sucursalId = null): array
    {
        $where  = "WHERE ac.ajus_cab_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND ac.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', ac.ajus_cab_fecha), 'MM/YYYY') AS mes,
                   ac.tipo_ajuste,
                   COUNT(*) AS cantidad
            FROM ajuste_cab ac
            {$where}
            GROUP BY DATE_TRUNC('month', ac.ajus_cab_fecha), ac.tipo_ajuste
            ORDER BY DATE_TRUNC('month', ac.ajus_cab_fecha), ac.tipo_ajuste
        ", $params);

        $meses    = array_values(array_unique(array_column((array)json_decode(json_encode($data)), 'mes')));
        $entradas = [];
        $salidas  = [];
        foreach ($meses as $mes) {
            $e = collect($data)->first(fn($r) => $r->mes === $mes && $r->tipo_ajuste === 'Entrada');
            $s = collect($data)->first(fn($r) => $r->mes === $mes && $r->tipo_ajuste === 'Salida');
            $entradas[] = $e ? (int)$e->cantidad : 0;
            $salidas[]  = $s ? (int)$s->cantidad : 0;
        }

        return [
            'id'           => 'aj_mes',
            'titulo'       => 'Ajustes por Mes (Entradas vs Salidas)',
            'tipo_grafico' => 'line',
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
            ],
            'labels'   => $meses,
            'datasets' => [
                ['label' => 'Entradas', 'data' => $entradas, 'color' => '#27ae60'],
                ['label' => 'Salidas',  'data' => $salidas,  'color' => '#e74c3c'],
            ],
            'columnas' => [
                ['key' => 'mes',         'label' => 'Mes'],
                ['key' => 'tipo_ajuste', 'label' => 'Tipo'],
                ['key' => 'cantidad',    'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function ajTopItems(string $desde, string $hasta, ?int $sucursalId = null, ?int $depositoId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE ac.ajus_cab_fecha BETWEEN :desde AND :hasta AND ac.ajus_cab_estado = 'CONFIRMADO'";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND ac.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($depositoId) {
            $where .= " AND ad.deposito_id = :deposito_id";
            $params['deposito_id'] = $depositoId;
        }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   ac.tipo_ajuste,
                   COALESCE(d.dep_nombre, 'Sin depósito') AS deposito,
                   SUM(ad.ajus_det_cantidad) AS total_cantidad
            FROM ajuste_det ad
            JOIN ajuste_cab ac ON ac.id = ad.ajuste_cab_id
            JOIN items      i  ON i.id  = ad.item_id
            LEFT JOIN deposito d ON d.id = ad.deposito_id
            {$where}
            GROUP BY i.item_decripcion, ac.tipo_ajuste, d.dep_nombre
            ORDER BY total_cantidad DESC
            LIMIT {$topN}
        ", $params);

        $pivot = [];
        foreach ($data as $row) {
            $pivot[$row->item] = ($pivot[$row->item] ?? 0) + (float)$row->total_cantidad;
        }
        arsort($pivot);
        $pivot = array_slice($pivot, 0, $topN, true);

        return [
            'id'           => 'aj_items',
            'titulo'       => 'Top ' . $topN . ' Ítems Más Ajustados',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
                ['id' => 'deposito', 'label' => 'Depósito', 'cascade' => 'sucursal'],
            ],
            'labels'   => array_keys($pivot),
            'datasets' => [['label' => 'Cantidad Ajustada', 'data' => array_values($pivot), 'color' => '#f39c12']],
            'columnas' => [
                ['key' => 'item',           'label' => 'Ítem'],
                ['key' => 'tipo_ajuste',    'label' => 'Tipo'],
                ['key' => 'deposito',       'label' => 'Depósito'],
                ['key' => 'total_cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function statsNotasCompra(string $desde, string $hasta): array
    {
        return [
            $this->ncPorTipo($desde, $hasta),
            $this->graficoEstados('notas_comp_cab', 'nota_comp_fecha', 'nota_comp_estado', 'Notas de Compra por Estado', '#8e44ad', $desde, $hasta),
            $this->ncPorMes($desde, $hasta),
            $this->ncTopProveedores($desde, $hasta),
            $this->ncTopItems($desde, $hasta),
        ];
    }

    private function ncPorTipo(string $desde, string $hasta): array
    {
        $data = DB::select("
            SELECT COALESCE(nota_comp_tipo, 'Sin tipo') AS tipo,
                   COUNT(*) AS cantidad
            FROM notas_comp_cab
            WHERE nota_comp_fecha BETWEEN :desde AND :hasta
            GROUP BY nota_comp_tipo ORDER BY cantidad DESC
        ", ['desde' => $desde, 'hasta' => $hasta]);

        return [
            'id'           => 'nc_tipo',
            'titulo'       => 'Notas de Compra por Tipo',
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->tipo, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [
                ['key' => 'tipo',     'label' => 'Tipo'],
                ['key' => 'cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function ncPorMes(string $desde, string $hasta, ?int $sucursalId = null, ?string $tipoNc = null): array
    {
        $where  = "WHERE nota_comp_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($tipoNc) {
            $where .= " AND nota_comp_tipo = :tipo";
            $params['tipo'] = $tipoNc;
        }

        $data = DB::select("
            SELECT TO_CHAR(DATE_TRUNC('month', nota_comp_fecha), 'MM/YYYY') AS mes,
                   COUNT(*) AS cantidad
            FROM notas_comp_cab
            {$where}
            GROUP BY DATE_TRUNC('month', nota_comp_fecha)
            ORDER BY DATE_TRUNC('month', nota_comp_fecha)
        ", $params);

        return [
            'id'           => 'nc_mes',
            'titulo'       => 'Notas de Compra por Mes',
            'tipo_grafico' => 'line',
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
                ['id' => 'tipo_nc',  'label' => 'Tipo'],
            ],
            'labels'   => array_map(fn($r) => $r->mes, $data),
            'datasets' => [['label' => 'Notas', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#16a085']],
            'columnas' => [
                ['key' => 'mes',      'label' => 'Mes'],
                ['key' => 'cantidad', 'label' => 'Cantidad'],
            ],
            'tabla' => $data,
        ];
    }

    private function ncTopProveedores(string $desde, string $hasta, ?int $sucursalId = null, int $topN = 10): array
    {
        $where  = "WHERE ncc.nota_comp_fecha BETWEEN :desde AND :hasta";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND ncc.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        $data = DB::select("
            SELECT COALESCE(prov.prov_razonsocial, 'Sin proveedor') AS proveedor,
                   COUNT(DISTINCT ncc.id) AS cantidad,
                   COALESCE(SUM(ncd.notas_comp_det_cantidad * ncd.notas_comp_det_costo), 0) AS monto_total
            FROM notas_comp_cab ncc
            JOIN compra_cab cc ON cc.id = ncc.compra_cab_id
            LEFT JOIN proveedores     prov ON prov.id = cc.proveedor_id
            LEFT JOIN notas_comp_det  ncd  ON ncd.notas_comp_cab_id = ncc.id
            {$where}
            GROUP BY prov.prov_razonsocial
            ORDER BY cantidad DESC
            LIMIT {$topN}
        ", $params);

        return [
            'id'           => 'nc_proveedor',
            'titulo'       => 'Top ' . $topN . ' Proveedores con Más Notas de Compra',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
            ],
            'labels'   => array_map(fn($r) => $r->proveedor, $data),
            'datasets' => [['label' => 'Notas', 'data' => array_map(fn($r) => (int)$r->cantidad, $data), 'color' => '#2980b9']],
            'columnas' => [
                ['key' => 'proveedor',   'label' => 'Proveedor'],
                ['key' => 'cantidad',    'label' => 'Notas'],
                ['key' => 'monto_total', 'label' => 'Monto Total (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    private function ncTopItems(string $desde, string $hasta, ?int $sucursalId = null, ?int $depositoId = null, int $topN = 10): array
    {
        $topN   = min(50, max(1, $topN));
        $where  = "WHERE ncc.nota_comp_fecha BETWEEN :desde AND :hasta AND ncc.nota_comp_estado = 'CONFIRMADO'";
        $params = ['desde' => $desde, 'hasta' => $hasta];
        if ($sucursalId) {
            $where .= " AND ncc.sucursal_id = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }
        if ($depositoId) {
            $where .= " AND ncd.deposito_id = :deposito_id";
            $params['deposito_id'] = $depositoId;
        }

        $data = DB::select("
            SELECT i.item_decripcion AS item,
                   COALESCE(d.dep_nombre, 'Sin depósito') AS deposito,
                   SUM(ncd.notas_comp_det_cantidad) AS total_cantidad,
                   COALESCE(SUM(ncd.notas_comp_det_cantidad * ncd.notas_comp_det_costo), 0) AS total_monto
            FROM notas_comp_det ncd
            JOIN notas_comp_cab ncc ON ncc.id = ncd.notas_comp_cab_id
            JOIN items          i   ON i.id   = ncd.item_id
            LEFT JOIN deposito  d   ON d.id   = ncd.deposito_id
            {$where}
            GROUP BY i.item_decripcion, d.dep_nombre
            ORDER BY total_cantidad DESC
            LIMIT {$topN}
        ", $params);

        $pivot = [];
        foreach ($data as $row) {
            $pivot[$row->item] = ($pivot[$row->item] ?? 0) + (float)$row->total_cantidad;
        }
        arsort($pivot);
        $pivot = array_slice($pivot, 0, $topN, true);

        return [
            'id'           => 'nc_items',
            'titulo'       => 'Top ' . $topN . ' Ítems Más Afectados por Notas de Compra',
            'tipo_grafico' => 'bar',
            'opciones'     => ['indexAxis' => 'y'],
            'filtros'      => [
                ['id' => 'sucursal', 'label' => 'Sucursal'],
                ['id' => 'deposito', 'label' => 'Depósito', 'cascade' => 'sucursal'],
            ],
            'labels'   => array_keys($pivot),
            'datasets' => [['label' => 'Cantidad', 'data' => array_values($pivot), 'color' => '#d35400']],
            'columnas' => [
                ['key' => 'item',           'label' => 'Ítem'],
                ['key' => 'deposito',       'label' => 'Depósito'],
                ['key' => 'total_cantidad', 'label' => 'Cantidad'],
                ['key' => 'total_monto',    'label' => 'Monto (Gs.)'],
            ],
            'tabla' => $data,
        ];
    }

    // ── Catálogos para selectores del frontend ─────────────────────────────────

    public function catalogos()
    {
        $sucursales = DB::select("
            SELECT id, suc_razon_social AS nombre
            FROM sucursal
            ORDER BY suc_razon_social
        ");

        $depositos = DB::select("
            SELECT d.id, d.dep_nombre AS nombre, d.sucursal_id, s.suc_razon_social AS sucursal
            FROM deposito d
            LEFT JOIN sucursal s ON s.id = d.sucursal_id
            ORDER BY d.dep_nombre
        ");

        $proveedores = DB::select("
            SELECT id, prov_razonsocial AS nombre
            FROM proveedores
            ORDER BY prov_razonsocial
        ");

        return response()->json([
            'sucursales'  => $sucursales,
            'depositos'   => $depositos,
            'proveedores' => $proveedores,
        ]);
    }
}
