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
        Schema::table('pedidos_detalles', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos_detalles', 'cantidad_stock')) $table->integer('cantidad_stock')->after('det_cantidad')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos_detalles', function (Blueprint $table) {
            //
        });
    }
};
