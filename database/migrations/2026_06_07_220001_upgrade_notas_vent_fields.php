<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // notas_vent_cab — campos que faltaban
        DB::statement("ALTER TABLE notas_vent_cab ADD COLUMN IF NOT EXISTS funcionario_id BIGINT NULL");
        DB::statement("ALTER TABLE notas_vent_cab ADD COLUMN IF NOT EXISTS timbrado_id BIGINT NULL");
        DB::statement("ALTER TABLE notas_vent_cab ADD COLUMN IF NOT EXISTS nota_vent_nro_comprobante VARCHAR(30) NULL");

        // notas_vent_det — deposito, marca, modelo
        DB::statement("ALTER TABLE notas_vent_det ADD COLUMN IF NOT EXISTS deposito_id BIGINT NULL");
        DB::statement("ALTER TABLE notas_vent_det ADD COLUMN IF NOT EXISTS marca_id BIGINT NULL");
        DB::statement("ALTER TABLE notas_vent_det ADD COLUMN IF NOT EXISTS modelo_id BIGINT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notas_vent_cab DROP COLUMN IF EXISTS timbrado_id");
        DB::statement("ALTER TABLE notas_vent_cab DROP COLUMN IF EXISTS nota_vent_nro_comprobante");
        DB::statement("ALTER TABLE notas_vent_det DROP COLUMN IF EXISTS marca_id");
        DB::statement("ALTER TABLE notas_vent_det DROP COLUMN IF EXISTS modelo_id");
    }
};
