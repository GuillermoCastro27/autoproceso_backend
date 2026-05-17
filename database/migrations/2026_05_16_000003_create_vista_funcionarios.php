<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW v_funcionarios AS
            SELECT
                f.id,
                f.fun_nom,
                f.fun_apellido,
                f.fun_ci,
                f.fun_direccion,
                f.fun_telefono,
                f.fun_correo,
                f.pais_id,
                f.ciudad_id,
                f.nacionalidad_id,
                f.created_at,
                f.updated_at,
                p.pais_descrpcion,
                ci.ciu_descripcion,
                n.nacio_descripcion
            FROM funcionario f
            JOIN paises p       ON p.id  = f.pais_id
            JOIN ciudades ci    ON ci.id = f.ciudad_id
            JOIN nacionalidad n ON n.id  = f.nacionalidad_id
            WHERE f.deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_funcionarios');
    }
};
