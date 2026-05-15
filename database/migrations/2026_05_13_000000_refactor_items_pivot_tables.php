<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Quitar marca_id y modelo_id de items (ahora se gestiona por tablas pivot)
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'marca_id')) $table->dropColumn('marca_id');
            if (Schema::hasColumn('items', 'modelo_id')) $table->dropColumn('modelo_id');
        });

        // Hacer nullable los campos descrip en las tablas pivot (campo auxiliar, no requerido desde UI)
        DB::statement('ALTER TABLE item_marca ALTER COLUMN item_marca_descrip DROP NOT NULL');
        DB::statement('ALTER TABLE item_modelo ALTER COLUMN item_modelo_descrip DROP NOT NULL');
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'marca_id')) $table->unsignedInteger('marca_id')->nullable();
            if (!Schema::hasColumn('items', 'modelo_id')) $table->unsignedInteger('modelo_id')->nullable();
        });

        DB::statement('ALTER TABLE item_marca ALTER COLUMN item_marca_descrip SET NOT NULL');
        DB::statement('ALTER TABLE item_modelo ALTER COLUMN item_modelo_descrip SET NOT NULL');
    }
};
