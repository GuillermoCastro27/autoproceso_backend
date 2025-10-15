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
            $table->string('diag_cab_observaciones', 200)->nullable();   // Observaciones técnicas o generales
            $table->timestamp('diag_cab_fecha');                         // Fecha del diagnóstico
            $table->string('diag_cab_estado', 50);                       // PENDIENTE / CONFIRMADO / ANULADO
            $table->string('diag_cab_prioridad', 50)->nullable();        // Alta, Media, Baja (heredada de recepción)
            $table->string('diag_cab_kilometraje')->nullable();          // Actual al momento del diagnóstico
            $table->string('diag_cab_nivel_combustible')->nullable();    // Nivel de combustible al momento del diagnóstico

            // Relaciones principales
            $table->unsignedBigInteger('user_id');                       // Técnico o encargado del diagnóstico
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('clientes_id')->nullable();       // Cliente del vehículo
            $table->foreign('clientes_id')->references('id')->on('clientes')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresa')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('empresa_id')->on('sucursal')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('recep_cab_id');                  // 🔗 Relación directa con la recepción
            $table->foreign('recep_cab_id')->references('id')->on('recep_cab')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('tipo_servicio_id');               // Tipo de servicio (Mecánica, Electricidad, etc.)
            $table->foreign('tipo_servicio_id')->references('id')->on('tipo_servicio')->onDelete('restrict')->onUpdate('cascade');
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
