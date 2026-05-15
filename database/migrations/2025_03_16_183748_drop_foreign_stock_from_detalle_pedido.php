<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('pedidos_detalles', function (Blueprint $table) {
            // Elimina la clave forÃ¡nea si existe
            // Elimina la columna stock_id
            if (Schema::hasColumn('pedidos_detalles', 'stock_id')) $table->dropColumn('stock_id'); 
        });
    }

    public function down() {
        Schema::table('pedidos_detalles', function (Blueprint $table) {
            // Si necesitas restaurar la columna y la relaciÃ³n
            if (!Schema::hasColumn('pedidos_detalles', 'stock_id')) $table->unsignedBigInteger('stock_id')->nullable();
        });
    }
};

