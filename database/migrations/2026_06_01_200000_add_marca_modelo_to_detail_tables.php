<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['presupuestos_detalles', 'orden_compra_det', 'compra_det'] as $tabla) {
            if (!Schema::hasTable($tabla)) continue;
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                if (!Schema::hasColumn($tabla, 'marca_id')) {
                    $table->unsignedBigInteger('marca_id')->nullable()->after('deposito_id');
                    $table->foreign('marca_id')->references('id')->on('marca');
                }
                if (!Schema::hasColumn($tabla, 'modelo_id')) {
                    $table->unsignedBigInteger('modelo_id')->nullable()->after('marca_id');
                    $table->foreign('modelo_id')->references('id')->on('modelo');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['compra_det', 'orden_compra_det', 'presupuestos_detalles'] as $tabla) {
            if (!Schema::hasTable($tabla)) continue;
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                if (Schema::hasColumn($tabla, 'modelo_id')) { $table->dropForeign([$tabla.'_modelo_id_foreign'] ); $table->dropColumn('modelo_id'); }
                if (Schema::hasColumn($tabla, 'marca_id'))  { $table->dropForeign([$tabla.'_marca_id_foreign']);  $table->dropColumn('marca_id');  }
            });
        }
    }
};
