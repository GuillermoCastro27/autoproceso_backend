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
        Schema::create('ctas_pagar', function (Blueprint $table) {
            $table->unsignedBigInteger('compra_cab_id');
            $table->foreign('compra_cab_id')->references('id')->on('compra_cab')->onDelete('restrict')->onUpdate('cascade');
            
            // Cambiar float por decimal para mayor precisión en montos monetarios
            $table->decimal('cta_pag_monto', 10, 2); // 10 dígitos, 2 decimales
            
            // Cambiar timestamp por date si solo necesitas la fecha
            $table->date('cta_pag_fecha');
            
            // Validar si cta_pag_cuota necesita ser string o algún otro tipo
            $table->string('cta_pag_cuota', 100)->nullable();
            
            // Cambiar a enum si solo tienes ciertos estados posibles
            $table->enum('cta_pag_estado', ['Pendiente', 'Pagada', 'Vencida']); // Por ejemplo
            
            $table->string('condicion_pago', 20)->nullable();
            $table->primary(['compra_cab_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ctas_pagar');
    }
};
