<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vaciar la tabla para evitar conflictos con datos viejos de estructura incorrecta
        DB::statement('TRUNCATE TABLE ctas_pagar RESTART IDENTITY CASCADE');

        // Quitar la primary key sobre compra_cab_id
        DB::statement('ALTER TABLE ctas_pagar DROP CONSTRAINT ctas_pagar_pkey');

        // Agregar id autoincremental como nueva PK
        DB::statement('ALTER TABLE ctas_pagar ADD COLUMN id BIGSERIAL PRIMARY KEY');

        // Agregar nro_cuota
        DB::statement('ALTER TABLE ctas_pagar ADD COLUMN nro_cuota INTEGER NOT NULL DEFAULT 1');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE ctas_pagar DROP COLUMN IF EXISTS nro_cuota');
        DB::statement('ALTER TABLE ctas_pagar DROP CONSTRAINT IF EXISTS ctas_pagar_pkey');
        DB::statement('ALTER TABLE ctas_pagar DROP COLUMN IF EXISTS id');
        DB::statement('ALTER TABLE ctas_pagar ADD PRIMARY KEY (compra_cab_id)');
    }
};
