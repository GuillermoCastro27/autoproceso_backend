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
        Schema::table("users", function(Blueprint $table){
            $table->unsignedInteger("perfil_id")->nullable(); 
            $table->foreign("perfil_id")->references("id")->on("perfiles")->onDelete("restrict")->onUpdate("cascade");
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