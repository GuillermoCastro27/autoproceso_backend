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
        Schema::create('contrato_serv_cab', function (Blueprint $table) {
            $table->id();
            // 🔹 Información general del contrato
            $table->timestamp('contrato_fecha');                    // Fecha de creación del contrato
            $table->date('contrato_fecha_inicio');                  // Inicio de vigencia
            $table->date('contrato_fecha_fin');                     // Fin de vigencia
            $table->timestamp('contrato_intervalo_fecha_vence')->nullable(); // Fecha o intervalo de vencimiento
            $table->string('contrato_estado', 20);                  // Ej.: Pendiente, Activo, Rescindido
            $table->string('contrato_condicion_pago', 50)->nullable(); // Contado, Crédito, Mensual, etc.
            $table->integer('contrato_cuotas')->nullable();         // Cantidad de cuotas (si aplica)
            $table->string('contrato_observacion', 200)->nullable();// Comentarios u observaciones
            $table->string('contrato_archivo_url', 255)->nullable();// Ruta del PDF generado automáticamente

            // 🔹 Relaciones principales
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresa')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('empresa_id')->on('sucursal')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('clientes_id');
            $table->foreign('clientes_id')->references('id')->on('clientes')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('tipo_servicio_id');
            $table->foreign('tipo_servicio_id')->references('id')->on('tipo_servicio')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrato_serv_cab');
    }
};
