<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('insumos_utilizados')) return;
        if (Schema::hasColumn('insumos_utilizados', 'ins_util_estado')) return;

        Schema::table('insumos_utilizados', function (Blueprint $table) {
            $table->string('ins_util_estado', 20)->default('PENDIENTE')->after('ins_util_costo');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('insumos_utilizados', 'ins_util_estado')) {
            Schema::table('insumos_utilizados', function (Blueprint $table) {
                $table->dropColumn('ins_util_estado');
            });
        }
    }
};
