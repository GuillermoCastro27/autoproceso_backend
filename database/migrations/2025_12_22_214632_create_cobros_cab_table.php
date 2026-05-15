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
        $table->string('nro_voucher', 50)->nullable(); // para cobros electrÃ³nicos
        $table->string('portador', 100)->nullable();
        $table->timestamp('fecha_cobro_diferido')->nullable();

        $table->unsignedBigInteger('forma_cobro_id');

        $table->unsignedBigInteger('clientes_id');

        $table->unsignedBigInteger('ventas_cab_id');

        $table->unsignedBigInteger('user_id');

        $table->unsignedBigInteger('caja_id');

        $table->unsignedBigInteger('empresa_id');

        $table->unsignedBigInteger('sucursal_id');

        $table->unsignedBigInteger('apertura_cierre_caja_id')->nullable();

        $table->unsignedBigInteger('entidad_emisora_id')->nullable();

        $table->unsignedBigInteger('marca_tarjeta_id')->nullable();

        $table->unsignedBigInteger('entidad_adherida_id')->nullable();
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
