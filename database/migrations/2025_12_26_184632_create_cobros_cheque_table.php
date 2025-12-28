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
        Schema::create('cobros_cheque', function (Blueprint $table) {

            $table->id();

            // =========================
            // 🔗 RELACIONES
            // =========================
            $table->unsignedBigInteger('cobros_cab_id');
            $table->unsignedBigInteger('entidad_emisora_id')->nullable();

            // =========================
            // 🧾 DATOS DEL CHEQUE
            // =========================
            $table->string('nro_cheque', 50)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('monto_cheque', 14, 2)->nullable();

            $table->string('estado_cheque', 20)->default('RECIBIDO');
            // RECIBIDO | DEPOSITADO | COBRADO | RECHAZADO | ANULADO

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
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros_cheque');
    }
};