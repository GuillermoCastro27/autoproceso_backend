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
        Schema::create('promociones_cab', function (Blueprint $table) {
            $table->id();
            $table->string('prom_cab_observaciones', 200)->nullable();   
            $table->string('prom_cab_nombre', 200)->nullable();   
            $table->timestamp('prom_cab_fecha_registro');                
            $table->timestamp('prom_cab_fecha_inicio');     
            $table->timestamp('prom_cab_fecha_fin');             
            $table->string('prom_cab_estado', 50);                 

            // Relaciones principales
            $table->unsignedBigInteger('user_id');                       

            $table->unsignedBigInteger('empresa_id');

            $table->unsignedBigInteger('sucursal_id');

            $table->unsignedBigInteger('tipo_promociones_id');               
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promociones_cab');
    }
};
