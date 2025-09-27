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
        Schema::create('tipo_descuentos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_desc_nombre',100);
            $table->string('tipo_desc_descrip',100);
            $table->timestamp('tipo_desc_fechaInicio');
            $table->timestamp('tipo_desc_fechaFin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_descuentos');
    }
};
