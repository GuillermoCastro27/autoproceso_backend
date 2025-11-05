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
        Schema::create('reclamo_cli_cab', function (Blueprint $table) {
            $table->id();
            // 🔹 Fechas del reclamo
            $table->timestamp('rec_cli_cab_fecha')->useCurrent();       // Fecha de creación
            $table->timestamp('rec_cli_cab_fecha_inicio')->nullable();  // Inicio del tratamiento
            $table->timestamp('rec_cli_cab_fecha_fin')->nullable();     // Fecha de resolución o cierre

            // 🔹 Estado y observaciones
            $table->string('rec_cli_cab_estado', 30);
            $table->string('rec_cli_cab_prioridad', 50);
            $table->string('rec_cli_cab_observacion', 255)->nullable();

            // 🔹 Relaciones principales
            $table->unsignedBigInteger('clientes_id');
            $table->foreign('clientes_id')->references('id')->on('clientes')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresa')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('empresa_id')->on('sucursal')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                  ->onDelete('restrict')->onUpdate('cascade');

            // 🔹 Relación futura con venta (nullable)
            $table->unsignedBigInteger('venta_cab_id')->nullable();
            //$table->foreign('venta_cab_id')->references('id')->on('venta_cab')
            //      ->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamo_cli_cab');
    }
};
