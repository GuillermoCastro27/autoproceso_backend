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
        Schema::table('apertura_cierre_caja', function (Blueprint $table) {
            $table->decimal('monto_apertura', 14, 2)
                ->default(0)
                ->after('fecha_apertura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apertura_cierre_caja', function (Blueprint $table) {
            //
        });
    }
};
