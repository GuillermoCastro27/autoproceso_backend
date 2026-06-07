<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'cli_tipo_persona')) {
                $table->string('cli_tipo_persona', 10)->default('FISICA')->after('cli_estado');
            }
            if (!Schema::hasColumn('clientes', 'cli_razon_social')) {
                $table->string('cli_razon_social', 200)->nullable()->after('cli_tipo_persona');
            }
        });

        // Recrear vista con los nuevos campos
        DB::unprepared('DROP VIEW IF EXISTS v_clientes');
        DB::unprepared("
            CREATE VIEW v_clientes AS
            SELECT
                c.id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                c.cli_correo,
                c.cli_estado,
                c.cli_tipo_persona,
                c.cli_razon_social,
                c.pais_id,
                c.ciudad_id,
                c.nacionalidad_id,
                c.created_at,
                c.updated_at,
                p.pais_descrpcion,
                ci.ciu_descripcion,
                n.nacio_descripcion
            FROM clientes c
            JOIN paises p        ON p.id  = c.pais_id
            JOIN ciudades ci     ON ci.id = c.ciudad_id
            JOIN nacionalidad n  ON n.id  = c.nacionalidad_id
            WHERE c.deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_clientes');

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['cli_tipo_persona', 'cli_razon_social']);
        });

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
    }
};
