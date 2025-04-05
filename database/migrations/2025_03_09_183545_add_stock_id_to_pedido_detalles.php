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
        Schema::table('pedidos_detalles', function (Blueprint $table) {     // Agregar la columna stock_id
                    $table->unsignedBigInteger('stock_id')->nullable(); 
        
                    // Agregar la clave foránea que referencia a stock
                    $table->foreign('stock_id')->references('stock_id')->on('stock')->onDelete('cascade');
                });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos_detalles', function (Blueprint $table) {
            //
        });
    }
};
