<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('entidad_adherida', function (Blueprint $table) {
            $table->id();

            // 🔗 Relaciones
            $table->unsignedBigInteger('entidad_emisora_id');
            $table->unsignedBigInteger('marca_tarjeta_id');

            // 🏷️ Datos propios (similar a entidad emisora)
            $table->string('ent_adh_nombre', 150);
            $table->string('ent_adh_direccion', 200)->nullable();
            $table->string('ent_adh_telefono', 50)->nullable();
            $table->string('ent_adh_email', 100)->nullable();

            // 🔒 Estado
            $table->string('ent_adh_estado', 20)->default('ACTIVO');

            $table->timestamps();

            // 🔑 Foreign Keys
            $table->foreign('entidad_emisora_id')
                  ->references('id')
                  ->on('entidad_emisora');

            $table->foreign('marca_tarjeta_id')
                  ->references('id')
                  ->on('marca_tarjeta');

            // 🔐 Evitar duplicados lógicos
            $table->unique(
                ['entidad_emisora_id', 'marca_tarjeta_id', 'ent_adh_nombre'],
                'entidad_adherida_unique'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('entidad_adherida');
    }
};
