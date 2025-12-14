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
        Schema::create('equipo_trabajo', function (Blueprint $table) {
            $table->id();
            $table->string('equipo_nombre', 100);
            $table->string('equipo_descripcion', 255)->nullable();
            $table->string('equipo_categoria', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipo_trabajo');
    }
};
