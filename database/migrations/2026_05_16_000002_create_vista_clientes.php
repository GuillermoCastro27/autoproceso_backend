<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW v_clientes AS
            SELECT
                c.id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                c.cli_correo,
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
    }
};
