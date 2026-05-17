<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW v_proveedores AS
            SELECT
                p.id,
                p.prov_razonsocial,
                p.prov_ruc,
                p.prov_direccion,
                p.prov_telefono,
                p.prov_correo,
                p.pais_id,
                p.ciudad_id,
                p.nacionalidad_id,
                p.created_at,
                p.updated_at,
                pa.pais_descrpcion,
                ci.ciu_descripcion,
                n.nacio_descripcion
            FROM proveedores p
            JOIN paises pa      ON pa.id = p.pais_id
            JOIN ciudades ci    ON ci.id = p.ciudad_id
            JOIN nacionalidad n ON n.id  = p.nacionalidad_id
            WHERE p.deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_proveedores');
    }
};
