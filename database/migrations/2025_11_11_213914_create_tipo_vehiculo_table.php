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
        Schema::create('tipo_vehiculo', function (Blueprint $table) {
            $table->id();
            $table->string('tip_veh_nombre', 50);
            $table->integer('tip_veh_capacidad')->nullable();
            $table->string('tip_veh_combustible', 30)->nullable();
            $table->string('tip_veh_categoria', 30)->nullable();
            $table->string('tip_veh_observacion', 200)->nullable();
            $table->unsignedBigInteger('marca_id');
            $table->foreign('marca_id')->references('id')->on('marca')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('modelo_id');
            $table->foreign('modelo_id')->references('id')->on('modelo')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_vehiculo');
    }
};
