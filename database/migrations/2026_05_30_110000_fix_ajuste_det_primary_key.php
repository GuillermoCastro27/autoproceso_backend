<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ajuste_det')) return;

        // Limpiar registros con deposito_id null (datos inválidos)
        DB::statement('DELETE FROM ajuste_det WHERE deposito_id IS NULL');

        // Eliminar PK compuesta actual (ajuste_cab_id, item_id)
        DB::statement('ALTER TABLE ajuste_det DROP CONSTRAINT IF EXISTS ajuste_det_pkey');

        // deposito_id NOT NULL para poder estar en la PK
        DB::statement('ALTER TABLE ajuste_det ALTER COLUMN deposito_id SET NOT NULL');

        // Nueva PK que incluye deposito_id → mismo ítem en distintos depósitos permitido
        DB::statement('ALTER TABLE ajuste_det ADD PRIMARY KEY (ajuste_cab_id, item_id, deposito_id)');
    }

    public function down(): void
    {
        if (!Schema::hasTable('ajuste_det')) return;
        DB::statement('ALTER TABLE ajuste_det DROP CONSTRAINT IF EXISTS ajuste_det_pkey');
        DB::statement('ALTER TABLE ajuste_det ALTER COLUMN deposito_id DROP NOT NULL');
        DB::statement('ALTER TABLE ajuste_det ADD PRIMARY KEY (ajuste_cab_id, item_id)');
    }
};
