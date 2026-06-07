<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presupuesto_serv_cab', function (Blueprint $table) {
            if (!Schema::hasColumn('presupuesto_serv_cab', 'recep_cab_id')) {
                $table->unsignedBigInteger('recep_cab_id')->nullable()->after('diagnostico_cab_id');
                $table->foreign('recep_cab_id')->references('id')->on('recep_cab')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('presupuesto_serv_cab', function (Blueprint $table) {
            $table->dropForeign(['recep_cab_id']);
            $table->dropColumn('recep_cab_id');
        });
    }
};
