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
        Schema::create('tipo_contrato', function (Blueprint $table) {
            $table->id();
            $table->string('tip_con_nombre', 100);

            $table->text('tip_con_objeto');
            $table->text('tip_con_alcance');
            $table->text('tip_con_garantia');
            $table->text('tip_con_responsabilidad');
            $table->text('tip_con_limitacion');
            $table->text('tip_con_fuerza_mayor');
            $table->text('tip_con_jurisdiccion');

            $table->string('tip_con_estado', 20)->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_contrato');
    }
};
