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
        Schema::create('cobros_tarjeta', function (Blueprint $table) {

            $table->id();

            // =========================
            // 🔗 RELACIONES
            // =========================
            $table->unsignedBigInteger('cobros_cab_id');
            $table->unsignedBigInteger('entidad_emisora_id')->nullable();
            $table->unsignedBigInteger('marca_tarjeta_id')->nullable();
            $table->unsignedBigInteger('entidad_adherida_id')->nullable();

            // =========================
            // 💳 DATOS DE TARJETA
            // =========================
            $table->string('nro_tarjeta', 20)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('nro_voucher', 50)->nullable();
            $table->decimal('monto_tarjeta', 14, 2)->nullable();

            $table->timestamps();

            // =========================
            // 🔑 CLAVES FORÁNEAS
            // =========================
            $table->foreign('cobros_cab_id')
                ->references('id')
                ->on('cobros_cab')
                ->onDelete('cascade');

            $table->foreign('entidad_emisora_id')
                ->references('id')
                ->on('entidad_emisora');

            $table->foreign('marca_tarjeta_id')
                ->references('id')
                ->on('marca_tarjeta');

            $table->foreign('entidad_adherida_id')
                ->references('id')
                ->on('entidad_adherida');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros_tarjeta');
    }
};