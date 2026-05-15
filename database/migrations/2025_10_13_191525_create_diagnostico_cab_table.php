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
        Schema::create('diagnostico_cab', function (Blueprint $table) {
            $table->id();
            $table->string('diag_cab_observaciones', 200)->nullable();   // Observaciones tÃ©cnicas o generales
            $table->timestamp('diag_cab_fecha');                         // Fecha del diagnÃ³stico
            $table->string('diag_cab_estado', 50);                       // PENDIENTE / CONFIRMADO / ANULADO
            $table->string('diag_cab_prioridad', 50)->nullable();        // Alta, Media, Baja (heredada de recepciÃ³n)
            $table->string('diag_cab_kilometraje')->nullable();          // Actual al momento del diagnÃ³stico
            $table->string('diag_cab_nivel_combustible')->nullable();    // Nivel de combustible al momento del diagnÃ³stico

            // Relaciones principales
            $table->unsignedBigInteger('user_id');                       // TÃ©cnico o encargado del diagnÃ³stico

            $table->unsignedBigInteger('clientes_id')->nullable();       // Cliente del vehÃ­culo

            $table->unsignedBigInteger('empresa_id');

            $table->unsignedBigInteger('sucursal_id');

            $table->unsignedBigInteger('recep_cab_id');                  // ðŸ”— RelaciÃ³n directa con la recepciÃ³n

            $table->unsignedBigInteger('tipo_diagnostico_id')->nullable();
            $table->unsignedBigInteger('tipo_servicio_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnostico_cab');
    }
};
