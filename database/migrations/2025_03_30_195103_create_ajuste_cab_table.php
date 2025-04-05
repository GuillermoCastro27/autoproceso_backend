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
        Schema::create('ajuste_cab', function (Blueprint $table) {
            $table->id();
            $table->timestamp('ajus_cab_fecha');
            $table->string('ajus_cab_estado', 50);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresa')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('empresa_id')->on('sucursal')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('motivo_ajuste_id');
            $table->foreign('motivo_ajuste_id')->references('id')->on('motivo_ajuste')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajuste_cab');
    }
};
