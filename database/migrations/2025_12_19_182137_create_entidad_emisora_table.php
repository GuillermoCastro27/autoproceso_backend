<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('entidad_emisora', function (Blueprint $table) {
            $table->id();

            $table->string('ent_emis_nombre', 150);
            $table->string('ent_emis_direccion', 200)->nullable();
            $table->string('ent_emis_telefono', 50)->nullable();
            $table->string('ent_emis_email', 100)->nullable();

            $table->string('ent_emis_estado', 20)->default('ACTIVO');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('entidad_emisora');
    }
};
