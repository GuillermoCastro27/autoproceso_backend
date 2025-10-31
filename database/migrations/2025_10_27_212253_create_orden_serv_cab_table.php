<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_serv_cab', function (Blueprint $table) {
            $table->id();

            // 📅 Fechas
            $table->timestamp('ord_serv_fecha');                   // Fecha de creación
            $table->timestamp('ord_serv_fecha_vence')->nullable(); // Fecha estimada de entrega

            // ⚙️ Estado y detalles operativos
            $table->string('ord_serv_estado', 50);                 // PENDIENTE, EN_PROCESO, FINALIZADA, ANULADA
            $table->string('ord_serv_tipo', 50)->nullable();       // Tipo de servicio (opcional)
            $table->string('ord_serv_observaciones', 200)->nullable(); // Comentarios generales

            // 🔗 Relaciones
            $table->unsignedBigInteger('presupuesto_serv_cab_id');
            $table->foreign('presupuesto_serv_cab_id')->references('id')->on('presupuesto_serv_cab')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('clientes_id');
            $table->foreign('clientes_id')->references('id')->on('clientes')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('user_id'); // usuario que genera la orden
            $table->foreign('user_id')->references('id')->on('users')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresa')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('empresa_id')->on('sucursal')
                  ->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_serv_cab');
    }
};
