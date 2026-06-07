<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE stock DISABLE TRIGGER ALL');
        DB::statement("UPDATE stock SET cantidad_minima = 10, cantidad_maxima = 50, updated_at = NOW()");
        DB::statement('ALTER TABLE stock ENABLE TRIGGER ALL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE stock DISABLE TRIGGER ALL');
        DB::statement("UPDATE stock SET cantidad_minima = 0, cantidad_maxima = 0, updated_at = NOW()");
        DB::statement('ALTER TABLE stock ENABLE TRIGGER ALL');
    }
};
