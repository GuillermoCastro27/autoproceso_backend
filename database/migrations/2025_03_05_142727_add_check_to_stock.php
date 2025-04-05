<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar restricción CHECK para limitar cantidad a 30
        DB::statement('ALTER TABLE stock ADD CONSTRAINT chk_cantidad_max CHECK (cantidad <= 30)');
    }

    public function down(): void
    {
        // Eliminar la restricción en caso de rollback
        DB::statement('ALTER TABLE stock DROP CONSTRAINT chk_cantidad_max');
    }
};

