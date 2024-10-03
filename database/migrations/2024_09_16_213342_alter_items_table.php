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
        Schema::table("items", function(Blueprint $table){
            $table->unsignedInteger("modelo_id")->nullable(); 
            $table->foreign("modelo_id")->references("id")->on("modelo")->onDelete("restrict")->onUpdate("cascade");
            $table->unsignedInteger("marca_id")->nullable(); 
            $table->foreign("marca_id")->references("id")->on("marca")->onDelete("restrict")->onUpdate("cascade");
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