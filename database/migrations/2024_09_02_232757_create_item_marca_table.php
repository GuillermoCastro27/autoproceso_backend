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
        Schema::create('item_marca', function (Blueprint $table) {
            $table->unsignedBigInteger('marca_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_marca_descrip'); // AsegÃºrate de que esto sea un string
            $table->timestamps();
    
            // Puedes agregar claves forÃ¡neas si es necesario
    
            $table->primary(['marca_id', 'item_id']); // Si la clave primaria es compuesta
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_marca');
    }
};
