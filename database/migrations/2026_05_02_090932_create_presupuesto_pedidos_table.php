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
        Schema::create('presupuesto_pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();
            $table->unsignedBigInteger('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->timestamp('pres_prov_ped_fecha_registro')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_pedidos');
    }
};
