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
        Schema::table('orden_compra_det', function (Blueprint $table) {
            $table->float('orden_compra_det_costo')->nullable(); // Cambiar aquÃ­ para permitir nulos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_compra_det', function (Blueprint $table) {
            if (Schema::hasColumn('orden_compra_det', 'orden_compra_det_costo')) $table->dropColumn('orden_compra_det_costo'); // AsegÃºrate de que esto estÃ© correcto
        });
    }
};

