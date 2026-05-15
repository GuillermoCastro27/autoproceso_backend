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

            // ðŸ“… Fechas
            $table->timestamp('ord_serv_fecha');                   // Fecha de creaciÃ³n
            $table->timestamp('ord_serv_fecha_vence')->nullable(); // Fecha estimada de entrega

            // âš™ï¸ Estado y detalles operativos
            $table->string('ord_serv_estado', 50);                 // PENDIENTE, EN_PROCESO, FINALIZADA, ANULADA
            $table->string('ord_serv_tipo', 50)->nullable();       // Tipo de servicio (opcional)
            $table->string('ord_serv_observaciones', 200)->nullable(); // Comentarios generales

            // ðŸ”— Relaciones
            $table->unsignedBigInteger('presupuesto_serv_cab_id');

            $table->unsignedBigInteger('clientes_id');

            $table->unsignedBigInteger('user_id'); // usuario que genera la orden

            $table->unsignedBigInteger('empresa_id');

            $table->unsignedBigInteger('sucursal_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_serv_cab');
    }
};
