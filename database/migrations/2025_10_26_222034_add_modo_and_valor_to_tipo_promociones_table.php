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
        Schema::table('tipo_promociones', function (Blueprint $table) {
            // 🔹 Tipo de modo de promoción: porcentaje, monto fijo, 2x1, etc.
            $table->string('tipo_prom_modo', 30)
                  ->nullable()
                  ->after('tipo_prom_descrip')
                  ->comment('Define el tipo de promoción: PORCENTAJE, MONTO_FIJO, 2X1, etc.');

            // 🔹 Valor de la promoción (porcentaje, monto, etc.)
            $table->decimal('tipo_prom_valor', 10, 2)
                  ->nullable()
                  ->after('tipo_prom_modo')
                  ->comment('Valor del descuento o promoción, dependiendo del modo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_promociones', function (Blueprint $table) {
            //
        });
    }
};
