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
        Schema::create('nota_remi_vent', function (Blueprint $table) {
            $table->id();
            $table->timestamp('nota_remi_vent_fecha');
            $table->string('nota_remi_vent_observaciones', 200)->nullable();
            $table->enum('nota_remi_vent_estado', ['PENDIENTE', 'CONFIRMADA', 'ANULADA'])
                ->default('PENDIENTE');
            $table->unsignedBigInteger('clientes_id');
            $table->unsignedBigInteger('ventas_cab_id')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('sucursal_id');

            // 🔹 Foreign keys
            $table->foreign('clientes_id')->references('id')->on('clientes')->onDelete('restrict');
            $table->foreign('ventas_cab_id')->references('id')->on('ventas_cab')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('empresa_id')->references('id')->on('empresa')->onDelete('restrict');
            $table->foreign('sucursal_id')->references('empresa_id')->on('sucursal')->onDelete('restrict');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_remi_vent');
    }
};
