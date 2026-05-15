<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── DEPOSITO ──────────────────────────────────────────
        DB::statement('TRUNCATE TABLE deposito RESTART IDENTITY CASCADE');
        DB::statement('ALTER TABLE deposito DROP CONSTRAINT IF EXISTS deposito_item_id_foreign');
        DB::statement('ALTER TABLE deposito DROP COLUMN IF EXISTS item_id');
        DB::statement('ALTER TABLE deposito DROP COLUMN IF EXISTS cantidad');
        DB::statement("ALTER TABLE deposito ADD COLUMN dep_nombre VARCHAR(200) NOT NULL DEFAULT 'Sin nombre'");
        DB::statement('ALTER TABLE deposito ADD COLUMN sucursal_id BIGINT');
        // FK not enforced (removed globally for fresh-DB compatibility)

        // ── STOCK ─────────────────────────────────────────────
        DB::statement('TRUNCATE TABLE stock RESTART IDENTITY CASCADE');
        DB::statement('ALTER TABLE stock DROP CONSTRAINT IF EXISTS chk_cantidad_max');
        DB::statement('ALTER TABLE stock DROP CONSTRAINT IF EXISTS stock_pkey');
        DB::statement('ALTER TABLE stock DROP COLUMN IF EXISTS stock_id');
        DB::statement('ALTER TABLE stock ADD COLUMN deposito_id BIGINT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE stock ADD COLUMN cantidad_minima DECIMAL(10,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE stock ADD COLUMN cantidad_maxima DECIMAL(10,2) NOT NULL DEFAULT 9999');
        DB::statement('ALTER TABLE stock ALTER COLUMN cantidad TYPE DECIMAL(10,2)');
        DB::statement('ALTER TABLE stock ADD CONSTRAINT stock_pkey PRIMARY KEY (deposito_id, item_id)');
        // FK not enforced (removed globally for fresh-DB compatibility)
    }

    public function down(): void
    {
        // Stock
        DB::statement('ALTER TABLE stock DROP CONSTRAINT IF EXISTS stock_deposito_id_foreign');
        DB::statement('ALTER TABLE stock DROP CONSTRAINT IF EXISTS stock_pkey');
        DB::statement('ALTER TABLE stock DROP COLUMN IF EXISTS deposito_id');
        DB::statement('ALTER TABLE stock DROP COLUMN IF EXISTS cantidad_minima');
        DB::statement('ALTER TABLE stock DROP COLUMN IF EXISTS cantidad_maxima');
        DB::statement('ALTER TABLE stock ADD COLUMN stock_id BIGSERIAL PRIMARY KEY');
        DB::statement('ALTER TABLE stock ADD CONSTRAINT chk_cantidad_max CHECK (cantidad <= 30)');

        // Deposito
        DB::statement('ALTER TABLE deposito DROP CONSTRAINT IF EXISTS deposito_sucursal_id_foreign');
        DB::statement('ALTER TABLE deposito DROP COLUMN IF EXISTS dep_nombre');
        DB::statement('ALTER TABLE deposito DROP COLUMN IF EXISTS sucursal_id');
        DB::statement('ALTER TABLE deposito ADD COLUMN item_id BIGINT');
        DB::statement('ALTER TABLE deposito ADD COLUMN cantidad INTEGER');
    }
};
