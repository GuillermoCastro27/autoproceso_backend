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
        Schema::create('apertura_cierre_caja', function (Blueprint $table) {
            $table->id();

            // 🔗 Relaciones básicas
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('caja_id');
            $table->unsignedBigInteger('user_id');

            // 🕒 Datos de apertura
            $table->timestamp('fecha_apertura');
            $table->string('estado', 20);

            // 🧾 Auditoría
            $table->timestamps();

            // 🔐 Claves foráneas
            $table->foreign('empresa_id')->references('id')->on('empresa');
            $table->foreign('sucursal_id')->references('empresa_id')->on('sucursal');
            $table->foreign('caja_id')->references('id')->on('caja');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apertura_cierre_caja');
    }
};
