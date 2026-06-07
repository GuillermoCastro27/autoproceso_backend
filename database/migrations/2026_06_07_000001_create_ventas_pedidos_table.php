<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas_pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ventas_cab_id');
            $table->unsignedBigInteger('pedidos_ventas_id');
            $table->timestamps();

            $table->foreign('ventas_cab_id')->references('id')->on('ventas_cab')->onDelete('cascade');
            $table->foreign('pedidos_ventas_id')->references('id')->on('pedidos_ventas')->onDelete('cascade');
            $table->unique(['ventas_cab_id', 'pedidos_ventas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas_pedidos');
    }
};
