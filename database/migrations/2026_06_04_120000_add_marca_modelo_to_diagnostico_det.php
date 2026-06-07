<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('diagnostico_det', 'marca_id')) {
            Schema::table('diagnostico_det', function (Blueprint $table) {
                $table->unsignedBigInteger('marca_id')->nullable()->after('tipo_impuesto_id');
                $table->foreign('marca_id')->references('id')->on('marca')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('diagnostico_det', 'modelo_id')) {
            Schema::table('diagnostico_det', function (Blueprint $table) {
                $table->unsignedBigInteger('modelo_id')->nullable()->after('marca_id');
                $table->foreign('modelo_id')->references('id')->on('modelo')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('diagnostico_det', function (Blueprint $table) {
            $table->dropForeignIfExists(['marca_id', 'modelo_id']);
            $table->dropColumnIfExists('marca_id');
            $table->dropColumnIfExists('modelo_id');
        });
    }
};
