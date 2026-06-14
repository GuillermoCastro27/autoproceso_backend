<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notas_vent_cab', function (Blueprint $table) {
            if (!Schema::hasColumn('notas_vent_cab', 'nota_vent_afecta_stock')) {
                $table->boolean('nota_vent_afecta_stock')->default(true)->after('nota_vent_tipo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notas_vent_cab', function (Blueprint $table) {
            if (Schema::hasColumn('notas_vent_cab', 'nota_vent_afecta_stock')) {
                $table->dropColumn('nota_vent_afecta_stock');
            }
        });
    }
};
