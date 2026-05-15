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
        Schema::create('reclamo_cli_cab', function (Blueprint $table) {
            $table->id();
            // ðŸ”¹ Fechas del reclamo
            $table->timestamp('rec_cli_cab_fecha')->useCurrent();       // Fecha de creaciÃ³n
            $table->timestamp('rec_cli_cab_fecha_inicio')->nullable();  // Inicio del tratamiento
            $table->timestamp('rec_cli_cab_fecha_fin')->nullable();     // Fecha de resoluciÃ³n o cierre

            // ðŸ”¹ Estado y observaciones
            $table->string('rec_cli_cab_estado', 30);
            $table->string('rec_cli_cab_prioridad', 50);
            $table->string('rec_cli_cab_observacion', 255)->nullable();

            // ðŸ”¹ Relaciones principales
            $table->unsignedBigInteger('clientes_id');

            $table->unsignedBigInteger('empresa_id');

            $table->unsignedBigInteger('sucursal_id');

            $table->unsignedBigInteger('user_id');

            // ðŸ”¹ RelaciÃ³n futura con venta (nullable)
            $table->unsignedBigInteger('venta_cab_id')->nullable();
            //      ->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamo_cli_cab');
    }
};
