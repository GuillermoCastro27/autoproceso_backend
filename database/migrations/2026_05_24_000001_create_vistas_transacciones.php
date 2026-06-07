<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->vCompras();
        $this->vComprasDetalle();
        $this->vVentas();
        $this->vVentasDetalle();
        $this->vOrdenesServicio();
        $this->vStock();
    }

    public function down(): void
    {
        foreach ([
            'v_compras', 'v_compras_detalle',
            'v_ventas',  'v_ventas_detalle',
            'v_ordenes_servicio',
            'v_stock',
        ] as $vista) {
            DB::unprepared("DROP VIEW IF EXISTS {$vista}");
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // COMPRAS
    // ─────────────────────────────────────────────────────────────────

    private function vCompras(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW v_compras AS
            -- Vista de cabecera de compras con datos del proveedor, empresa,
            -- sucursal, funcionario y totales calculados desde el detalle.
            SELECT
                cc.id,
                cc.comp_fecha,
                cc.comp_estado,
                cc.comp_cant_cuota,
                cc.condicion_pago,
                cc.comp_timbrado,
                cc.comp_intervalo_fecha_vence,
                cc.created_at,
                cc.updated_at,

                -- Proveedor
                cc.proveedor_id,
                p.prov_razonsocial,
                p.prov_ruc,
                p.prov_telefono,

                -- Empresa y sucursal
                cc.empresa_id,
                e.emp_razon_social,
                cc.sucursal_id,
                s.suc_razon_social,

                -- Funcionario responsable
                cc.funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido          AS funcionario,

                -- Totales agregados desde compra_det
                COUNT(cd.item_id)                           AS cantidad_items,
                COALESCE(SUM(cd.comp_det_cantidad * cd.comp_det_costo), 0) AS total_compra

            FROM compra_cab cc
            JOIN proveedores  p  ON p.id  = cc.proveedor_id
            JOIN empresa      e  ON e.id  = cc.empresa_id
            JOIN sucursal     s  ON s.id  = cc.sucursal_id
            JOIN funcionario  f  ON f.id  = cc.funcionario_id
            LEFT JOIN compra_det cd ON cd.compra_cab_id = cc.id

            GROUP BY
                cc.id, cc.comp_fecha, cc.comp_estado, cc.comp_cant_cuota,
                cc.condicion_pago, cc.comp_timbrado, cc.comp_intervalo_fecha_vence,
                cc.created_at, cc.updated_at,
                cc.proveedor_id, p.prov_razonsocial, p.prov_ruc, p.prov_telefono,
                cc.empresa_id, e.emp_razon_social,
                cc.sucursal_id, s.suc_razon_social,
                cc.funcionario_id, f.fun_nom, f.fun_apellido;

            COMMENT ON VIEW v_compras IS
                'Cabecera de compras con proveedor, empresa, sucursal, funcionario '
                'y totales calculados (items y monto) desde compra_det.';
        ");
    }

    private function vComprasDetalle(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW v_compras_detalle AS
            -- Vista de líneas de compra con datos del ítem, impuesto,
            -- depósito destino y datos de la cabecera de compra.
            SELECT
                cd.compra_cab_id,
                cd.item_id,
                cd.comp_det_cantidad,
                cd.comp_det_costo,
                cd.comp_det_cantidad * cd.comp_det_costo   AS subtotal,
                cd.deposito_id,
                cd.tipo_impuesto_id,
                cd.created_at,

                -- Cabecera
                cc.comp_fecha,
                cc.comp_estado,
                cc.proveedor_id,
                p.prov_razonsocial,

                -- Ítem
                i.item_decripcion,
                i.item_precio,

                -- Impuesto
                ti.tip_imp_nom,
                ti.tipo_imp_tasa,
                ROUND((cd.comp_det_cantidad * cd.comp_det_costo
                      * ti.tipo_imp_tasa / 100)::numeric, 0) AS monto_impuesto,

                -- Depósito destino
                COALESCE(d.dep_nombre, 'Sin depósito')     AS dep_nombre,
                COALESCE(s.suc_razon_social, '—')          AS suc_razon_social

            FROM compra_det cd
            JOIN compra_cab    cc ON cc.id  = cd.compra_cab_id
            JOIN proveedores    p ON p.id   = cc.proveedor_id
            JOIN items          i ON i.id   = cd.item_id
            JOIN tipo_impuesto ti ON ti.id  = cd.tipo_impuesto_id
            LEFT JOIN deposito  d ON d.id   = cd.deposito_id
            LEFT JOIN sucursal  s ON s.id   = d.sucursal_id;

            COMMENT ON VIEW v_compras_detalle IS
                'Líneas de compra con datos completos del ítem, impuesto aplicado, '
                'subtotal, monto de impuesto y depósito destino.';
        ");
    }

    // ─────────────────────────────────────────────────────────────────
    // VENTAS
    // ─────────────────────────────────────────────────────────────────

    private function vVentas(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW v_ventas AS
            -- Vista de cabecera de ventas con cliente, empresa, sucursal,
            -- funcionario y totales calculados desde ventas_det.
            SELECT
                vc.id,
                vc.vent_fecha,
                vc.vent_estado,
                vc.vent_cant_cuota,
                vc.condicion_pago,
                vc.vent_intervalo_fecha_vence,
                vc.created_at,
                vc.updated_at,

                -- Cliente
                vc.clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_nombre || ' ' || c.cli_apellido       AS cliente,
                c.cli_ruc,
                c.cli_telefono,

                -- Empresa y sucursal
                vc.empresa_id,
                e.emp_razon_social,
                vc.sucursal_id,
                s.suc_razon_social,

                -- Funcionario responsable
                vc.funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido          AS funcionario,

                -- Totales agregados desde ventas_det
                COUNT(vd.item_id)                           AS cantidad_items,
                COALESCE(SUM(vd.vent_det_cantidad * vd.vent_det_precio), 0) AS total_venta

            FROM ventas_cab vc
            JOIN clientes    c  ON c.id  = vc.clientes_id
            JOIN empresa     e  ON e.id  = vc.empresa_id
            JOIN sucursal    s  ON s.id  = vc.sucursal_id
            JOIN funcionario f  ON f.id  = vc.funcionario_id
            LEFT JOIN ventas_det vd ON vd.ventas_cab_id = vc.id

            GROUP BY
                vc.id, vc.vent_fecha, vc.vent_estado, vc.vent_cant_cuota,
                vc.condicion_pago, vc.vent_intervalo_fecha_vence,
                vc.created_at, vc.updated_at,
                vc.clientes_id, c.cli_nombre, c.cli_apellido, c.cli_ruc, c.cli_telefono,
                vc.empresa_id, e.emp_razon_social,
                vc.sucursal_id, s.suc_razon_social,
                vc.funcionario_id, f.fun_nom, f.fun_apellido;

            COMMENT ON VIEW v_ventas IS
                'Cabecera de ventas con cliente, empresa, sucursal, funcionario '
                'y totales calculados (items y monto) desde ventas_det.';
        ");
    }

    private function vVentasDetalle(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW v_ventas_detalle AS
            -- Vista de líneas de venta con datos del ítem, impuesto,
            -- depósito origen y datos de la cabecera de venta.
            SELECT
                vd.ventas_cab_id,
                vd.item_id,
                vd.vent_det_cantidad,
                vd.vent_det_precio,
                vd.vent_det_cantidad * vd.vent_det_precio   AS subtotal,
                vd.deposito_id,
                vd.tipo_impuesto_id,
                vd.created_at,

                -- Cabecera
                vc.vent_fecha,
                vc.vent_estado,
                vc.clientes_id,
                c.cli_nombre || ' ' || c.cli_apellido       AS cliente,

                -- Ítem
                i.item_decripcion,
                i.item_costo,

                -- Impuesto
                ti.tip_imp_nom,
                ti.tipo_imp_tasa,
                ROUND((vd.vent_det_cantidad * vd.vent_det_precio
                      * ti.tipo_imp_tasa / 100)::numeric, 0) AS monto_impuesto,

                -- Depósito origen
                COALESCE(d.dep_nombre, 'Sin depósito')      AS dep_nombre,
                COALESCE(s.suc_razon_social, '—')           AS suc_razon_social

            FROM ventas_det vd
            JOIN ventas_cab    vc ON vc.id  = vd.ventas_cab_id
            JOIN clientes       c ON c.id   = vc.clientes_id
            JOIN items          i ON i.id   = vd.item_id
            JOIN tipo_impuesto ti ON ti.id  = vd.tipo_impuesto_id
            LEFT JOIN deposito  d ON d.id   = vd.deposito_id
            LEFT JOIN sucursal  s ON s.id   = d.sucursal_id;

            COMMENT ON VIEW v_ventas_detalle IS
                'Líneas de venta con datos completos del ítem, impuesto aplicado, '
                'subtotal, monto de impuesto y depósito origen.';
        ");
    }

    // ─────────────────────────────────────────────────────────────────
    // SERVICIOS
    // ─────────────────────────────────────────────────────────────────

    private function vOrdenesServicio(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW v_ordenes_servicio AS
            -- Vista de órdenes de servicio con cliente, empresa, sucursal,
            -- funcionario, equipo de trabajo, tipo de vehículo, tipo de
            -- diagnóstico y totales calculados desde orden_serv_det.
            SELECT
                oc.id,
                oc.ord_serv_fecha,
                oc.ord_serv_fecha_vence,
                oc.ord_serv_estado,
                oc.ord_serv_tipo,
                oc.ord_serv_observaciones,
                oc.created_at,
                oc.updated_at,

                -- Cliente
                oc.clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_nombre || ' ' || c.cli_apellido       AS cliente,
                c.cli_telefono,
                c.cli_ruc,

                -- Empresa y sucursal
                oc.empresa_id,
                e.emp_razon_social,
                oc.sucursal_id,
                s.suc_razon_social,

                -- Funcionario responsable
                oc.funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido          AS funcionario,

                -- Equipo de trabajo asignado
                oc.equipo_trabajo_id,
                COALESCE(et.equipo_nombre, 'Sin equipo')    AS equipo_trabajo,

                -- Tipo de vehículo
                oc.tipo_vehiculo_id,
                COALESCE(tv.tip_veh_nombre, 'N/A')         AS tipo_vehiculo,

                -- Tipo de diagnóstico
                oc.tipo_diagnostico_id,
                COALESCE(td.tipo_diag_nombre, 'N/A')       AS tipo_diagnostico,

                -- Totales agregados desde orden_serv_det
                COUNT(od.item_id)                           AS cantidad_items,
                COALESCE(
                    SUM(od.orden_serv_det_cantidad * od.orden_serv_det_costo), 0
                )                                           AS total_servicio

            FROM orden_serv_cab oc
            JOIN clientes        c  ON c.id  = oc.clientes_id
            JOIN empresa         e  ON e.id  = oc.empresa_id
            JOIN sucursal        s  ON s.id  = oc.sucursal_id
            JOIN funcionario     f  ON f.id  = oc.funcionario_id
            LEFT JOIN equipo_trabajo   et ON et.id = oc.equipo_trabajo_id
            LEFT JOIN tipo_vehiculo    tv ON tv.id = oc.tipo_vehiculo_id
            LEFT JOIN tipo_diagnostico td ON td.id = oc.tipo_diagnostico_id
            LEFT JOIN orden_serv_det   od ON od.orden_serv_cab_id = oc.id

            GROUP BY
                oc.id, oc.ord_serv_fecha, oc.ord_serv_fecha_vence, oc.ord_serv_estado,
                oc.ord_serv_tipo, oc.ord_serv_observaciones, oc.created_at, oc.updated_at,
                oc.clientes_id, c.cli_nombre, c.cli_apellido, c.cli_telefono, c.cli_ruc,
                oc.empresa_id, e.emp_razon_social,
                oc.sucursal_id, s.suc_razon_social,
                oc.funcionario_id, f.fun_nom, f.fun_apellido,
                oc.equipo_trabajo_id, et.equipo_nombre,
                oc.tipo_vehiculo_id, tv.tip_veh_nombre,
                oc.tipo_diagnostico_id, td.tipo_diag_nombre;

            COMMENT ON VIEW v_ordenes_servicio IS
                'Órdenes de servicio con cliente, equipo asignado, tipo de vehículo, '
                'diagnóstico y totales calculados desde orden_serv_det.';
        ");
    }

    // ─────────────────────────────────────────────────────────────────
    // STOCK
    // ─────────────────────────────────────────────────────────────────

    private function vStock(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW v_stock AS
            -- Vista de stock con datos del ítem, depósito y sucursal.
            -- Incluye columna calculada estado_stock para identificar
            -- ítems con stock bajo, normal o en máximo.
            SELECT
                s.item_id,
                s.deposito_id,
                s.cantidad,
                s.cantidad_minima,
                s.cantidad_maxima,
                s.created_at,
                s.updated_at,

                -- Ítem
                i.item_decripcion,
                i.item_costo,
                i.item_precio,

                -- Depósito y sucursal
                d.dep_nombre,
                d.sucursal_id,
                su.suc_razon_social,

                -- Estado calculado del stock
                CASE
                    WHEN s.cantidad_minima > 0
                         AND s.cantidad <= s.cantidad_minima THEN 'BAJO'
                    WHEN s.cantidad_maxima > 0
                         AND s.cantidad >= s.cantidad_maxima THEN 'MÁXIMO'
                    ELSE 'NORMAL'
                END                                         AS estado_stock,

                -- Valor total en inventario
                s.cantidad * i.item_costo                   AS valor_inventario

            FROM stock s
            JOIN items    i  ON i.id  = s.item_id
            JOIN deposito d  ON d.id  = s.deposito_id
            JOIN sucursal su ON su.id = d.sucursal_id
            WHERE i.deleted_at IS NULL;

            COMMENT ON VIEW v_stock IS
                'Stock por ítem y depósito con estado calculado (BAJO/NORMAL/MÁXIMO), '
                'valor total en inventario y datos de ubicación (depósito y sucursal).';
        ");
    }
};
