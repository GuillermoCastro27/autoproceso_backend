<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas de negocio que recibirán el trigger de auditoría.
     * Se excluyen tablas de infraestructura del sistema (users, perfiles,
     * modulos, accesos, permisos, audits, failed_jobs, etc.).
     */
    private array $tablas = [
        // ── Referenciales ────────────────────────────────────────────
        'tipos',            'paises',           'ciudades',
        'nacionalidad',     'marca',            'modelo',
        'tipo_impuesto',    'tipo_vehiculo',    'tipo_servicio',
        'tipo_diagnostico', 'tipo_promociones', 'tipo_descuentos',
        'tipo_contrato',    'equipo_trabajo',   'motivo_ajuste',
        'entidad_emisora',  'marca_tarjeta',    'forma_cobro',
        'empresa',          'sucursal',         'deposito',
        'caja',

        // ── Entidades principales ────────────────────────────────────
        'clientes',         'proveedores',      'funcionario',
        'items',            'item_marca',       'item_modelo',
        'stock',            'entidad_adherida',

        // ── Compras ──────────────────────────────────────────────────
        'orden_compra_cab', 'orden_compra_det',
        'compra_cab',       'compra_det',
        'ctas_pagar',       'libro_compras',
        'nota_remi_comp',   'nota_remi_com_det',
        'notas_comp_cab',   'notas_comp_det',
        'solicitudes_cab',  'solicitudes_det',
        'recep_cab',        'recep_det',

        // ── Ventas ───────────────────────────────────────────────────
        'pedidos_ventas',   'pedidos_ventas_det',
        'ventas_cab',       'ventas_det',
        'libro_ventas',

        // ── Pedidos y presupuestos ───────────────────────────────────
        'pedidos',          'pedidos_detalles',
        'presupuestos',     'presupuestos_detalles',
        'presupuesto_pedidos',

        // ── Cobros ───────────────────────────────────────────────────
        'cobros_cab',       'cobros_det',
        'cobros_tarjeta',   'cobros_cheque',
        'cobro_efectivo',   'cobros_ctas_cobrar',

        // ── Servicios ────────────────────────────────────────────────
        'presupuesto_serv_cab', 'presupuesto_serv_det',
        'orden_serv_cab',       'orden_serv_det',
        'orden_serv_venta',
        'contrato_serv_cab',    'contrato_serv_det',
        'diagnostico_det',

        // ── Ajustes de inventario ────────────────────────────────────
        'ajuste_cab',       'ajuste_det',

        // ── Caja ─────────────────────────────────────────────────────
        'arqueo_caja',      'apertura_cierre_caja',

        // ── Reclamos y promociones ───────────────────────────────────
        'reclamo_cli_cab',  'reclamo_cli_det',
        'promociones_cab',  'descuentos_cab',
    ];

    // ─────────────────────────────────────────────────────────────────
    // UP
    // ─────────────────────────────────────────────────────────────────
    public function up(): void
    {
        $this->crearTablaAuditoria();
        $this->crearFuncionAuditoria();
        $this->crearFuncionValidarStock();
        $this->aplicarTriggerAuditoria();
        $this->aplicarTriggerStock();
    }

    // ─────────────────────────────────────────────────────────────────
    // DOWN
    // ─────────────────────────────────────────────────────────────────
    public function down(): void
    {
        foreach ($this->tablas as $tabla) {
            if (Schema::hasTable($tabla)) {
                DB::unprepared("DROP TRIGGER IF EXISTS trg_auditoria_{$tabla} ON {$tabla};");
            }
        }

        if (Schema::hasTable('stock')) {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_validar_stock ON stock;');
        }

        DB::unprepared('DROP FUNCTION IF EXISTS fn_auditoria_transacciones();');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_validar_stock();');
        DB::unprepared('DROP TABLE IF EXISTS auditoria_transacciones;');
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVADOS
    // ─────────────────────────────────────────────────────────────────

    private function crearTablaAuditoria(): void
    {
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS auditoria_transacciones (
                id               BIGSERIAL    PRIMARY KEY,
                tabla_nombre     VARCHAR(100) NOT NULL,
                operacion        VARCHAR(10)  NOT NULL
                                 CONSTRAINT chk_operacion
                                 CHECK (operacion IN ('INSERT', 'UPDATE', 'DELETE')),
                registro_id      BIGINT,
                datos_anteriores JSONB,
                datos_nuevos     JSONB,
                usuario_bd       VARCHAR(100) NOT NULL DEFAULT current_user,
                fecha_hora       TIMESTAMP    NOT NULL DEFAULT NOW()
            );

            COMMENT ON TABLE  auditoria_transacciones                IS 'Registro centralizado de auditoría: toda inserción, modificación o eliminación sobre las tablas de negocio queda registrada aquí.';
            COMMENT ON COLUMN auditoria_transacciones.tabla_nombre   IS 'Nombre de la tabla afectada por la operación.';
            COMMENT ON COLUMN auditoria_transacciones.operacion      IS 'Tipo de operación DML ejecutada: INSERT, UPDATE o DELETE.';
            COMMENT ON COLUMN auditoria_transacciones.registro_id    IS 'Valor del campo id del registro afectado.';
            COMMENT ON COLUMN auditoria_transacciones.datos_anteriores IS 'Estado completo del registro ANTES de la operación (NULL en INSERT).';
            COMMENT ON COLUMN auditoria_transacciones.datos_nuevos   IS 'Estado completo del registro DESPUÉS de la operación (NULL en DELETE).';
            COMMENT ON COLUMN auditoria_transacciones.usuario_bd     IS 'Usuario de PostgreSQL que ejecutó la operación.';
            COMMENT ON COLUMN auditoria_transacciones.fecha_hora     IS 'Fecha y hora exacta (servidor) en que se ejecutó la operación.';

            CREATE INDEX IF NOT EXISTS idx_aud_tabla
                ON auditoria_transacciones (tabla_nombre);

            CREATE INDEX IF NOT EXISTS idx_aud_fecha
                ON auditoria_transacciones (fecha_hora DESC);

            CREATE INDEX IF NOT EXISTS idx_aud_registro
                ON auditoria_transacciones (tabla_nombre, registro_id);

            CREATE INDEX IF NOT EXISTS idx_aud_operacion
                ON auditoria_transacciones (operacion);
        ");
    }

    private function crearFuncionAuditoria(): void
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_auditoria_transacciones()
            RETURNS TRIGGER AS \$fn\$
            /*
             * FUNCIÓN: fn_auditoria_transacciones
             * PROPÓSITO: Registrar cualquier operación DML sobre tablas de negocio
             *            en la tabla centralizada auditoria_transacciones.
             * ACTIVACIÓN: AFTER INSERT OR UPDATE OR DELETE (FOR EACH ROW).
             * PARÁMETROS IMPLÍCITOS DE TRIGGER:
             *   TG_OP        — tipo de operación ('INSERT', 'UPDATE', 'DELETE').
             *   TG_TABLE_NAME — nombre de la tabla que disparó el trigger.
             *   NEW           — fila resultante (disponible en INSERT y UPDATE).
             *   OLD           — fila previa (disponible en UPDATE y DELETE).
             * MANEJO DE ERRORES:
             *   Si el registro en la tabla de auditoría falla (ej. error de disco),
             *   se emite un WARNING sin cancelar la transacción principal.
             */
            DECLARE
                v_id BIGINT;
            BEGIN
                BEGIN
                    v_id := CASE
                        WHEN TG_OP = 'DELETE' THEN OLD.id
                        ELSE NEW.id
                    END;

                    INSERT INTO auditoria_transacciones (
                        tabla_nombre,
                        operacion,
                        registro_id,
                        datos_anteriores,
                        datos_nuevos,
                        usuario_bd,
                        fecha_hora
                    ) VALUES (
                        TG_TABLE_NAME,
                        TG_OP,
                        v_id,
                        CASE WHEN TG_OP IN ('UPDATE', 'DELETE')
                             THEN row_to_json(OLD)::jsonb ELSE NULL END,
                        CASE WHEN TG_OP IN ('INSERT', 'UPDATE')
                             THEN row_to_json(NEW)::jsonb ELSE NULL END,
                        current_user,
                        NOW()
                    );

                EXCEPTION
                    WHEN OTHERS THEN
                        RAISE WARNING
                            'fn_auditoria_transacciones: no se pudo registrar auditoría '
                            '(tabla=%, op=%, id=%). Error: %',
                            TG_TABLE_NAME, TG_OP, v_id, SQLERRM;
                END;

                RETURN CASE WHEN TG_OP = 'DELETE' THEN OLD ELSE NEW END;
            END;
            \$fn\$ LANGUAGE plpgsql;

            COMMENT ON FUNCTION fn_auditoria_transacciones() IS
                'Trigger genérico de auditoría. Se aplica AFTER INSERT/UPDATE/DELETE '
                'en todas las tablas de negocio. Serializa OLD y NEW como JSONB.';
        ");
    }

    private function crearFuncionValidarStock(): void
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_validar_stock()
            RETURNS TRIGGER AS \$fn\$
            /*
             * FUNCIÓN: fn_validar_stock
             * PROPÓSITO: Garantizar la integridad de la tabla stock a nivel de BD,
             *            independientemente de la capa de aplicación.
             * ACTIVACIÓN: BEFORE INSERT OR UPDATE (FOR EACH ROW) en la tabla stock.
             * VALIDACIONES:
             *   1. La cantidad no puede ser negativa.
             *   2. Si existe cantidad_maxima > 0, la cantidad no puede superarla.
             * MANEJO DE ERRORES:
             *   Lanza EXCEPTION con código personalizado para que la aplicación
             *   pueda identificar el tipo de error y mostrar el mensaje adecuado.
             */
            BEGIN
                -- Validación 1: stock negativo
                IF NEW.cantidad < 0 THEN
                    RAISE EXCEPTION
                        'STOCK_NEGATIVO: La cantidad de stock no puede ser negativa. '
                        '(tabla=%, id=%, cantidad=%)',
                        TG_TABLE_NAME, NEW.id, NEW.cantidad
                        USING ERRCODE = 'P0001';
                END IF;

                -- Validación 2: exceso sobre cantidad máxima
                IF NEW.cantidad_maxima IS NOT NULL
                   AND NEW.cantidad_maxima > 0
                   AND NEW.cantidad > NEW.cantidad_maxima THEN
                    RAISE EXCEPTION
                        'STOCK_EXCEDE_MAXIMO: La cantidad (%) supera el máximo '
                        'permitido (%). (tabla=%, id=%)',
                        NEW.cantidad, NEW.cantidad_maxima,
                        TG_TABLE_NAME, NEW.id
                        USING ERRCODE = 'P0002';
                END IF;

                RETURN NEW;

            EXCEPTION
                WHEN SQLSTATE 'P0001' OR SQLSTATE 'P0002' THEN
                    RAISE;
                WHEN OTHERS THEN
                    RAISE EXCEPTION
                        'fn_validar_stock: error inesperado en tabla % id %. Error: %',
                        TG_TABLE_NAME, NEW.id, SQLERRM;
            END;
            \$fn\$ LANGUAGE plpgsql;

            COMMENT ON FUNCTION fn_validar_stock() IS
                'Trigger BEFORE INSERT/UPDATE en stock. Previene cantidad negativa '
                'y exceso sobre cantidad_maxima. Refuerza las validaciones de la '
                'capa de aplicación directamente en la base de datos.';
        ");
    }

    private function aplicarTriggerAuditoria(): void
    {
        foreach ($this->tablas as $tabla) {
            if (!Schema::hasTable($tabla)) {
                continue;
            }

            $trigger = "trg_auditoria_{$tabla}";

            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger} ON {$tabla};");
            DB::unprepared("
                CREATE TRIGGER {$trigger}
                AFTER INSERT OR UPDATE OR DELETE ON {$tabla}
                FOR EACH ROW
                EXECUTE FUNCTION fn_auditoria_transacciones();

                COMMENT ON TRIGGER {$trigger} ON {$tabla} IS
                    'Auditoría automática: registra INSERT/UPDATE/DELETE en auditoria_transacciones.';
            ");
        }
    }

    private function aplicarTriggerStock(): void
    {
        if (!Schema::hasTable('stock')) {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS trg_validar_stock ON stock;');
        DB::unprepared("
            CREATE TRIGGER trg_validar_stock
            BEFORE INSERT OR UPDATE ON stock
            FOR EACH ROW
            EXECUTE FUNCTION fn_validar_stock();

            COMMENT ON TRIGGER trg_validar_stock ON stock IS
                'Validación BEFORE INSERT/UPDATE: previene stock negativo y exceso '
                'sobre cantidad_maxima directamente en la base de datos.';
        ");
    }
};
