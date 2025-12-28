<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ctas_cobrar', function (Blueprint $table) {
            $table->id();

            // Relación con la venta
            $table->unsignedBigInteger('ventas_cab_id');

            // Datos de la cuota
            $table->integer('nro_cuota');
            $table->decimal('cta_cob_monto', 14, 2);
            $table->date('cta_cob_fecha_vencimiento');

            // Estado de la cuota
            $table->string('cta_cob_estado', 20)->default('PENDIENTE');

            // Condición de pago
            $table->string('condicion_pago', 20);

            // FK ventas
            $table->foreign('ventas_cab_id')
                ->references('id')->on('ventas_cab')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // Evitar duplicar una misma cuota
            $table->unique(['ventas_cab_id', 'nro_cuota']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ctas_cobrar');
    }
};
