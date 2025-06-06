<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('libro_compras', function (Blueprint $table) {
            $table->enum('libC_tipo_nota', ['NC', 'ND'])->nullable()
              ->after('libC_cuota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('libro_compras', function (Blueprint $table) {
            //
        });
    }
};
