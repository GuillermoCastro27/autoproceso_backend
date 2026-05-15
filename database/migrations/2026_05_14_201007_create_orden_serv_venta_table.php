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
            $table->unsignedBigInteger('orden_serv_cab_id');
            $table->unsignedBigInteger('contrato_serv_cab_id')->nullable();
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
