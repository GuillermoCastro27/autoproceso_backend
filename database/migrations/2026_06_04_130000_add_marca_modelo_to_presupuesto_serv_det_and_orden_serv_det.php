<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['presupuesto_serv_det', 'orden_serv_det'] as $table) {
            if (!Schema::hasColumn($table, 'marca_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->unsignedBigInteger('marca_id')->nullable()->after('tipo_impuesto_id');
                    $t->foreign('marca_id')->references('id')->on('marca')->nullOnDelete();
                });
            }
            if (!Schema::hasColumn($table, 'modelo_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->unsignedBigInteger('modelo_id')->nullable()->after('marca_id');
                    $t->foreign('modelo_id')->references('id')->on('modelo')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['presupuesto_serv_det', 'orden_serv_det'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeignIfExists(['marca_id', 'modelo_id']);
                $t->dropColumnIfExists('marca_id');
                $t->dropColumnIfExists('modelo_id');
            });
        }
    }
};
