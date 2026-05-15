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
        Schema::table('ventas_cab', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas_cab', 'pedidos_ventas_id')) $table->unsignedBigInteger('pedidos_ventas_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ventas_cab', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas_cab', 'pedidos_ventas_id')) $table->unsignedBigInteger('pedidos_ventas_id')->nullable(false)->change();
        });
    }
};
