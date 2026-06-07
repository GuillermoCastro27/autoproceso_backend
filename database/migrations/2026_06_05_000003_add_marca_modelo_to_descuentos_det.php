<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('descuentos_det', function (Blueprint $table) {
            if (!Schema::hasColumn('descuentos_det', 'marca_id')) {
                $table->unsignedBigInteger('marca_id')->nullable()->after('tipo_impuesto_id');
            }
            if (!Schema::hasColumn('descuentos_det', 'modelo_id')) {
                $table->unsignedBigInteger('modelo_id')->nullable()->after('marca_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('descuentos_det', function (Blueprint $table) {
            $table->dropColumn(['marca_id', 'modelo_id']);
        });
    }
};
