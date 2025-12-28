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
    Schema::create('cobros_cab', function (Blueprint $table) {
        $table->id();

        $table->timestamp('cobro_fecha'); // fecha y hora del cobro
        $table->string('cobro_estado', 20)->default('PENDIENTE'); // PENDIENTE, CONFIRMADO, ANULADO
        $table->decimal('cobro_importe', 15, 2)->default(0);
        $table->string('cobro_observacion', 200)->nullable();

        $table->string('numero_documento', 50)->nullable(); // cheque, transferencia, etc.
        $table->string('nro_voucher', 50)->nullable(); // para cobros electrónicos
        $table->string('portador', 100)->nullable();
        $table->timestamp('fecha_cobro_diferido')->nullable();

        $table->unsignedBigInteger('forma_cobro_id');
        $table->foreign('forma_cobro_id')
            ->references('id')
            ->on('forma_cobro')
            ->onDelete('restrict')
            ->onUpdate('cascade');

        $table->unsignedBigInteger('clientes_id');
        $table->foreign('clientes_id')
            ->references('id')
            ->on('clientes')
            ->onDelete('restrict')
            ->onUpdate('cascade');

        $table->unsignedBigInteger('ventas_cab_id');
        $table->foreign('ventas_cab_id')
            ->references('id')
            ->on('ventas_cab')
            ->onDelete('restrict')
            ->onUpdate('cascade');

        $table->unsignedBigInteger('user_id');
        $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('restrict')
            ->onUpdate('cascade');

        $table->unsignedBigInteger('caja_id');
        $table->foreign('caja_id')
            ->references('id')
            ->on('caja')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        $table->unsignedBigInteger('empresa_id');
        $table->foreign('empresa_id')
            ->references('id')
            ->on('empresa')
            ->onDelete('restrict')
            ->onUpdate('cascade');

        $table->unsignedBigInteger('sucursal_id');
        $table->foreign('sucursal_id')
            ->references('empresa_id')
            ->on('sucursal')
            ->onDelete('restrict')
            ->onUpdate('cascade');

        $table->unsignedBigInteger('apertura_cierre_caja_id')->nullable();
        $table->foreign('apertura_cierre_caja_id')
            ->references('id')
            ->on('apertura_cierre_caja')
            ->onDelete('restrict')
            ->onUpdate('cascade');

        $table->unsignedBigInteger('entidad_emisora_id')->nullable();
        $table->foreign('entidad_emisora_id')->references('id')->on('entidad_emisora')->onDelete('restrict')->onUpdate('cascade');

        $table->unsignedBigInteger('marca_tarjeta_id')->nullable();
        $table->foreign('marca_tarjeta_id')
            ->references('id')
            ->on('marca_tarjeta')
            ->onDelete('restrict')
            ->onUpdate('cascade');

        $table->unsignedBigInteger('entidad_adherida_id')->nullable();
        $table->foreign('entidad_adherida_id')
            ->references('id')
            ->on('entidad_adherida')
            ->onDelete('restrict')
            ->onUpdate('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros_cab');
    }
};
