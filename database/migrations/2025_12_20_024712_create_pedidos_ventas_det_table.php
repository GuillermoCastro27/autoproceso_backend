<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_ventas_det', function (Blueprint $table) {

            // 🔑 Clave compuesta
            $table->unsignedBigInteger('pedidos_ventas_id');
            $table->unsignedBigInteger('item_id');

            // 📦 Cantidades
            $table->integer('det_cantidad');
            $table->integer('cantidad_stock'); 

            // ✅ PK compuesta (igual a tu idea de compras)
            $table->primary(['pedidos_ventas_id', 'item_id']);

            // 🔗 Foreign Keys
            $table->foreign('pedidos_ventas_id')
                ->references('id')
                ->on('pedidos_ventas')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_ventas_det');
    }
};
