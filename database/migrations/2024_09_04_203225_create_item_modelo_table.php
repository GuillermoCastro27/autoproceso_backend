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
        Schema::create('item_modelo', function (Blueprint $table) {
            $table->unsignedBigInteger('modelo_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_modelo_descrip'); // Asegúrate de que esto sea un string
            $table->timestamps();
    
            // Puedes agregar claves foráneas si es necesario
            $table->foreign('modelo_id')->references('id')->on('modelo');
            $table->foreign('item_id')->references('id')->on('items');
    
            $table->primary(['modelo_id', 'item_id']); // Si la clave primaria es compuesta
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_modelo');
    }
};
