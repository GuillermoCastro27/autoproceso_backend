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

            // ðŸ“… Fechas
            $table->dateTime('vent_intervalo_fecha_vence')->nullable();
            $table->timestamp('vent_fecha');

            // ðŸ“Œ Estado y condiciÃ³n de pago
            $table->string('vent_estado', 50);
            $table->string('vent_cant_cuota')->nullable();
            $table->string('condicion_pago', 20)->nullable();

            // ðŸ‘¤ Usuario
            $table->unsignedBigInteger('user_id');

            // ðŸ§¾ Pedido de venta
            $table->unsignedBigInteger('pedidos_ventas_id');

            // ðŸ‘¥ Cliente
            $table->unsignedBigInteger('clientes_id')->nullable();

            // ðŸ¢ Empresa
            $table->unsignedBigInteger('empresa_id');

            // ðŸ¬ Sucursal
            $table->unsignedBigInteger('sucursal_id');

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
