<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('libro_compras', function (Blueprint $table) {
            $table->unsignedBigInteger('compra_cab_id')->primary(); // Se usa como clave principal
            
            $table->unsignedTinyInteger('libC_dia'); // Día del mes (1-31)
            $table->decimal('libC_monto', 10, 2);
            $table->date('libC_fecha');
            $table->string('condicion_pago', 20)->nullable();
            $table->string('libC_cuota', 100)->nullable();

            // Relación con proveedor
            $table->unsignedBigInteger('proveedor_id');
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('restrict')->onUpdate('cascade');

            // Relación con tipo de impuesto
            $table->unsignedBigInteger('tipo_impuesto_id');
            $table->foreign('tipo_impuesto_id')->references('id')->on('tipo_impuesto')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('libro_compras');
    }
};

