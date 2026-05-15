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
        Schema::table('ajuste_cab', function (Blueprint $table) {
            if (!Schema::hasColumn('ajuste_cab', 'tipo_ajuste')) $table->string('tipo_ajuste')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ajuste_cab', function (Blueprint $table) {
            //
        });
    }
};
