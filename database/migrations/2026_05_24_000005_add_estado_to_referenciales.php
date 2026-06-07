<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columnas = [
            ['clientes',        'cli_estado'],
            ['funcionario',     'fun_estado'],
            ['items',           'item_estado'],
            ['marca',           'marc_estado'],
            ['modelo',          'modelo_estado'],
            ['empresa',         'emp_estado'],
            ['sucursal',        'suc_estado'],
            ['deposito',        'dep_estado'],
            ['caja',            'caja_estado'],
            ['equipo_trabajo',  'equipo_estado'],
            ['tipo_servicio',   'tipo_serv_estado'],
            ['tipo_promociones','tipo_prom_estado'],
            ['tipo_descuentos', 'tipo_desc_estado'],
            ['tipo_impuesto',   'tip_imp_estado'],
            ['tipo_vehiculo',   'tip_veh_estado'],
            ['tipo_diagnostico','tipo_diag_estado'],
            ['motivo_ajuste',   'estado'],
            ['forma_cobro',     'for_cob_estado'],
            ['marca_tarjeta',   'marca_estado'],
            ['tipos',           'tipo_estado'],
        ];

        foreach ($columnas as [$tabla, $col]) {
            if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, $col)) {
                Schema::table($tabla, function (Blueprint $t) use ($col) {
                    $t->string($col, 10)->default('activo');
                });
            }
        }

        // Recrear vistas para incluir el campo estado
        DB::unprepared('DROP VIEW IF EXISTS v_clientes');
        DB::unprepared("
            CREATE VIEW v_clientes AS
            SELECT c.id, c.cli_nombre, c.cli_apellido, c.cli_ruc, c.cli_direccion,
                   c.cli_telefono, c.cli_correo, c.pais_id, c.ciudad_id, c.nacionalidad_id,
                   c.cli_estado, c.created_at, c.updated_at,
                   p.pais_descrpcion, ci.ciu_descripcion, n.nacio_descripcion
            FROM clientes c
            JOIN paises p       ON p.id  = c.pais_id
            JOIN ciudades ci    ON ci.id = c.ciudad_id
            JOIN nacionalidad n ON n.id  = c.nacionalidad_id
            WHERE c.deleted_at IS NULL
        ");

        DB::unprepared('DROP VIEW IF EXISTS v_funcionarios');
        DB::unprepared("
            CREATE VIEW v_funcionarios AS
            SELECT f.id, f.fun_nom, f.fun_apellido, f.fun_ci, f.fun_direccion,
                   f.fun_telefono, f.fun_correo, f.pais_id, f.ciudad_id, f.nacionalidad_id,
                   f.fun_estado, f.created_at, f.updated_at,
                   p.pais_descrpcion, ci.ciu_descripcion, n.nacio_descripcion
            FROM funcionario f
            JOIN paises p       ON p.id  = f.pais_id
            JOIN ciudades ci    ON ci.id = f.ciudad_id
            JOIN nacionalidad n ON n.id  = f.nacionalidad_id
            WHERE f.deleted_at IS NULL
        ");

        DB::unprepared('DROP VIEW IF EXISTS v_items');
        DB::unprepared("
            CREATE VIEW v_items AS
            SELECT i.id, i.item_decripcion, i.item_costo, i.item_precio,
                   i.tipo_id, i.tipo_impuesto_id, i.item_estado, i.created_at, i.updated_at,
                   t.tipo_descripcion, ti.tip_imp_nom,
                   STRING_AGG(DISTINCT m.marc_nom,  ', ' ORDER BY m.marc_nom)   AS marcas,
                   STRING_AGG(DISTINCT mo.modelo_nom, ', ' ORDER BY mo.modelo_nom) AS modelos
            FROM items i
            JOIN tipos t          ON t.id  = i.tipo_id
            JOIN tipo_impuesto ti ON ti.id = i.tipo_impuesto_id
            LEFT JOIN item_marca im   ON im.item_id  = i.id
            LEFT JOIN marca m          ON m.id         = im.marca_id
            LEFT JOIN item_modelo imo  ON imo.item_id  = i.id
            LEFT JOIN modelo mo         ON mo.id        = imo.modelo_id
            WHERE i.deleted_at IS NULL
            GROUP BY i.id, i.item_decripcion, i.item_costo, i.item_precio,
                     i.tipo_id, i.tipo_impuesto_id, i.item_estado, i.created_at, i.updated_at,
                     t.tipo_descripcion, ti.tip_imp_nom
        ");
    }

    public function down(): void
    {
        $columnas = [
            ['clientes',        'cli_estado'],
            ['funcionario',     'fun_estado'],
            ['items',           'item_estado'],
            ['marca',           'marc_estado'],
            ['modelo',          'modelo_estado'],
            ['empresa',         'emp_estado'],
            ['sucursal',        'suc_estado'],
            ['deposito',        'dep_estado'],
            ['caja',            'caja_estado'],
            ['equipo_trabajo',  'equipo_estado'],
            ['tipo_servicio',   'tipo_serv_estado'],
            ['tipo_promociones','tipo_prom_estado'],
            ['tipo_descuentos', 'tipo_desc_estado'],
            ['tipo_impuesto',   'tip_imp_estado'],
            ['tipo_vehiculo',   'tip_veh_estado'],
            ['tipo_diagnostico','tipo_diag_estado'],
            ['motivo_ajuste',   'estado'],
            ['forma_cobro',     'for_cob_estado'],
            ['marca_tarjeta',   'marca_estado'],
            ['tipos',           'tipo_estado'],
        ];

        foreach ($columnas as [$tabla, $col]) {
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, $col)) {
                Schema::table($tabla, function (Blueprint $t) use ($col) {
                    $t->dropColumn($col);
                });
            }
        }
    }
};
