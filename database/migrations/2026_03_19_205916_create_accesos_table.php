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
        Schema::create('accesos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permisos_id'); 
            $table->foreign('permisos_id')->references('id')->on('permisos')->onDelete('cascade');
            $table->unsignedBigInteger('perfiles_id');
            $table->foreign('perfiles_id')->references('id')->on('perfiles')->onDelete('cascade');
            $table->string('acc_estado');  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accesos');
    }
};
