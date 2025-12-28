<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('libro_ventas', function (Blueprint $table) {

            // PK = venta
            $table->unsignedBigInteger('ventas_cab_id')->primary();

            // Importes
            $table->decimal('libV_monto', 10, 2);
            $table->date('libV_fecha');

            // Condición de pago
            $table->string('condicion_pago', 20)->nullable();
            $table->string('libV_cuota', 100)->nullable();

            // Cliente (datos congelados)
            $table->unsignedBigInteger('clientes_id');
            $table->string('cli_nombre', 150)->nullable();
            $table->string('cli_apellido', 150)->nullable();
            $table->string('cli_ruc', 20)->nullable();

            // Impuesto
            $table->unsignedBigInteger('tipo_impuesto_id');
            $table->string('tip_imp_nom', 100)->nullable();

            // FK cliente
            $table->foreign('clientes_id')
                ->references('id')->on('clientes')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // FK tipo impuesto
            $table->foreign('tipo_impuesto_id')
                ->references('id')->on('tipo_impuesto')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('libro_ventas');
    }
};
