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
        Schema::create('tipo_promociones', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_prom_descrip',100);
            $table->string('tipo_prom_nombre',100);
            $table->timestamp('tipo_prom_fechaInicio');
            $table->timestamp('tipo_prom_fechaFin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_promociones');
    }
};
