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
        Schema::create('stock', function (Blueprint $table) {
            $table->id('stock_id'); // ID de stock
            $table->unsignedBigInteger('item_id'); // ID del artÃ­culo
            $table->integer('cantidad'); // Cantidad de stock
            $table->timestamps(); // Fecha de creaciÃ³n y actualizaciÃ³n

            // RelaciÃ³n con la tabla items
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};

