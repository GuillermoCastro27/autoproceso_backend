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
        Schema::create('arqueo_caja', function (Blueprint $table) {
            $table->id();

            $table->string('arqueo_nro')->unique();
            $table->timestamp('arqueo_fecha');

            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('apertura_cierre_caja_id');
            $table->unsignedBigInteger('user_id');

            $table->enum('tipo_arqueo', ['EFECTIVO', 'CHEQUE', 'TARJETA', 'TOTAL']);

            $table->decimal('total_efectivo', 15, 2)->default(0);
            $table->decimal('total_cheque', 15, 2)->default(0);
            $table->decimal('total_tarjeta', 15, 2)->default(0);
            $table->decimal('total_general', 15, 2)->default(0);

            $table->enum('estado', ['PENDIENTE', 'CONFIRMADO'])->default('PENDIENTE');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arqueo_caja');
    }
};
