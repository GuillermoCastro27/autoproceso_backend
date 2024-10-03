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
        Schema::table("clientes", function(Blueprint $table){
            $table->unsignedInteger("nacionalidad_id")->nullable(); 
            $table->foreign("nacionalidad_id")->references("id")->on("nacionalidad")->onDelete("restrict")->onUpdate("cascade");
            $table->unsignedInteger("ciudad_id")->nullable(); 
            $table->foreign("ciudad_id")->references("id")->on("ciudades")->onDelete("restrict")->onUpdate("cascade");
            $table->unsignedInteger("pais_id")->nullable(); 
            $table->foreign("pais_id")->references("id")->on("paises")->onDelete("restrict")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
