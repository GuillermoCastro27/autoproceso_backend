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
        Schema::create('orden_serv_venta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ventas_cab_id');
            $table->foreign('ventas_cab_id')
                  ->references('id')->on('ventas_cab')
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('orden_serv_cab_id');
            $table->foreign('orden_serv_cab_id')
                  ->references('id')->on('orden_serv_cab')
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('contrato_serv_cab_id')->nullable();
            $table->foreign('contrato_serv_cab_id')
                  ->references('id')->on('contrato_serv_cab')
                  ->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_serv_venta');
    }
};
