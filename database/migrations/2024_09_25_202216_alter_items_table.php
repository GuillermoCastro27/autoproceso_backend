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
        Schema::table('items', function (Blueprint $table) {
            // Modificar las columnas existentes para que no acepten valores NULL
            $table->unsignedBigInteger('tipo_impuesto_id')->nullable(false)->change();
            $table->unsignedBigInteger('marca_id')->nullable(false)->change();
            $table->unsignedBigInteger('modelo_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
