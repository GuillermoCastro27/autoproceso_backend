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
        Schema::create('ventas_cab', function (Blueprint $table) {
            $table->id();

            // 📅 Fechas
            $table->dateTime('vent_intervalo_fecha_vence')->nullable();
            $table->timestamp('vent_fecha');

            // 📌 Estado y condición de pago
            $table->string('vent_estado', 50);
            $table->string('vent_cant_cuota')->nullable();
            $table->string('condicion_pago', 20)->nullable();

            // 👤 Usuario
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // 🧾 Pedido de venta
            $table->unsignedBigInteger('pedidos_ventas_id');
            $table->foreign('pedidos_ventas_id')
                ->references('id')->on('pedidos_ventas')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // 👥 Cliente
            $table->unsignedBigInteger('clientes_id')->nullable();
            $table->foreign('clientes_id')
                ->references('id')->on('clientes')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // 🏢 Empresa
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')
                ->references('id')->on('empresa')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // 🏬 Sucursal
            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')
                ->references('empresa_id')->on('sucursal')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas_cab');
    }
};
