<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_cobros AS
            SELECT
                cc.id,

                -- Timestamp original para filtrado por fecha
                cc.cobro_fecha                                              AS cobro_fecha_ts,

                -- Fechas formateadas para display
                TO_CHAR(cc.cobro_fecha, 'YYYY-MM-DD HH24:MI:SS')           AS cobro_fecha,
                COALESCE(
                    TO_CHAR(cc.fecha_cobro_diferido, 'YYYY-MM-DD HH24:MI:SS'),
                    'N/A'
                )                                                           AS fecha_cobro_diferido,

                -- Estado / importes
                cc.cobro_estado,
                cc.cobro_importe,
                COALESCE(cc.cobro_observacion, '')                          AS cobro_observacion,

                -- Efectivo
                COALESCE(ce.monto_efectivo, 0)                             AS monto_efectivo,

                -- Datos del cobro
                COALESCE(cc.numero_documento, 'N/A')                       AS numero_documento,
                COALESCE(cc.nro_voucher, 'N/A')                            AS nro_voucher,
                COALESCE(cc.portador, 'N/A')                               AS portador,

                -- Forma de cobro
                fc.id                                                       AS forma_cobro_id,
                fc.for_cob_descripcion                                      AS forma_cobro,

                -- Cliente
                cli.id                                                      AS clientes_id,
                cli.cli_nombre,
                cli.cli_apellido,
                cli.cli_ruc,
                cli.cli_telefono,
                cli.cli_correo,
                cli.cli_direccion,

                -- Venta asociada
                cc.ventas_cab_id,
                'VENTA NRO: ' || TO_CHAR(cc.ventas_cab_id, '0000000')      AS venta_nro,

                -- Empresa / Sucursal
                cc.empresa_id,
                e.emp_razon_social,
                cc.sucursal_id,
                s.suc_razon_social,

                -- Funcionario
                f.fun_nom || ' ' || f.fun_apellido                          AS funcionario,

                -- Caja
                ac.id                                                       AS apertura_cierre_caja_id,
                ac.estado                                                   AS aper_cier_estado,
                ca.caja_descripcion                                         AS caja,

                -- Entidades opcionales
                cc.entidad_emisora_id,
                COALESCE(ee.ent_emis_nombre, 'N/A')                        AS entidad_emisora,

                cc.marca_tarjeta_id,
                COALESCE(mt.marca_nombre, 'N/A')                           AS marca_tarjeta,

                cc.entidad_adherida_id,
                COALESCE(ea.ent_adh_nombre, 'N/A')                         AS entidad_adherida

            FROM cobros_cab cc
            JOIN forma_cobro fc              ON fc.id  = cc.forma_cobro_id
            JOIN clientes cli                ON cli.id = cc.clientes_id
            JOIN empresa e                   ON e.id   = cc.empresa_id
            JOIN sucursal s                  ON s.id   = cc.sucursal_id
            JOIN funcionario f               ON f.id   = cc.funcionario_id
            JOIN apertura_cierre_caja ac     ON ac.id  = cc.apertura_cierre_caja_id
            JOIN caja ca                     ON ca.id  = ac.caja_id
            LEFT JOIN entidad_emisora ee     ON ee.id  = cc.entidad_emisora_id
            LEFT JOIN marca_tarjeta mt       ON mt.id  = cc.marca_tarjeta_id
            LEFT JOIN entidad_adherida ea    ON ea.id  = cc.entidad_adherida_id
            LEFT JOIN cobro_efectivo ce      ON ce.cobros_cab_id = cc.id
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_cobros");
    }
};
