<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW v_items AS
            SELECT
                i.id,
                i.item_decripcion,
                i.item_costo,
                i.item_precio,
                i.tipo_id,
                i.tipo_impuesto_id,
                i.created_at,
                i.updated_at,
                t.tipo_descripcion,
                ti.tip_imp_nom,
                STRING_AGG(DISTINCT m.marc_nom,  ', ' ORDER BY m.marc_nom)  AS marcas,
                STRING_AGG(DISTINCT mo.modelo_nom, ', ' ORDER BY mo.modelo_nom) AS modelos
            FROM items i
            JOIN tipos t          ON t.id  = i.tipo_id
            JOIN tipo_impuesto ti ON ti.id = i.tipo_impuesto_id
            LEFT JOIN item_marca im  ON im.item_id  = i.id
            LEFT JOIN marca m        ON m.id         = im.marca_id
            LEFT JOIN item_modelo imo ON imo.item_id = i.id
            LEFT JOIN modelo mo       ON mo.id        = imo.modelo_id
            WHERE i.deleted_at IS NULL
            GROUP BY
                i.id, i.item_decripcion, i.item_costo, i.item_precio,
                i.tipo_id, i.tipo_impuesto_id, i.created_at, i.updated_at,
                t.tipo_descripcion, ti.tip_imp_nom
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_items');
    }
};
