<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('pedidos_detalles', function (Blueprint $table) {
            // Elimina la clave foránea si existe
            $table->dropForeign(['stock_id']); 
            // Elimina la columna stock_id
            $table->dropColumn('stock_id'); 
        });
    }

    public function down() {
        Schema::table('pedidos_detalles', function (Blueprint $table) {
            // Si necesitas restaurar la columna y la relación
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->foreign('stock_id')->references('id')->on('stock')->onDelete('cascade');
        });
    }
};

