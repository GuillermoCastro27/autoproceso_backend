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
        Schema::create('deposito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id'); // ID del artículo
            $table->integer('cantidad'); // Cantidad en depósito
            $table->timestamps(); // Fecha de creación y actualización

            // Relación con la tabla items
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposito');
    }
};
