<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuesto_serv_cab', function (Blueprint $table) {
            $table->id();
            $table->string('pres_serv_cab_observaciones', 200)->nullable();
            $table->timestamp('pres_serv_cab_fecha');
            $table->timestamp('pres_serv_cab_fecha_vence');
            $table->string('pres_serv_cab_estado', 50);

            // Relaciones principales
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresa')
                  ->onDelete('restrict')->onUpdate('cascade');

            // ⚙️ Mantengo tu forma de relación con sucursal (por empresa_id)
            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('empresa_id')->on('sucursal')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('diagnostico_cab_id');
            $table->foreign('diagnostico_cab_id')->references('id')->on('diagnostico_cab')
                  ->onDelete('restrict')->onUpdate('cascade');

            // 🔹 Relaciones opcionales
            $table->unsignedBigInteger('promociones_cab_id')->nullable();
            $table->foreign('promociones_cab_id')->references('id')->on('promociones_cab')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('descuentos_cab_id')->nullable();
            $table->foreign('descuentos_cab_id')->references('id')->on('descuentos_cab')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('clientes_id')->nullable();
            $table->foreign('clientes_id')->references('id')->on('clientes')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuesto_serv_cab');
    }
};
