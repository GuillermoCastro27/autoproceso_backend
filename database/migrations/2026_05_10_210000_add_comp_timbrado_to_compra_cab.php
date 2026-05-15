<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compra_cab', function (Blueprint $table) {
            $table->string('comp_timbrado', 20)->nullable()->after('condicion_pago');
        });
    }

    public function down(): void
    {
        Schema::table('compra_cab', function (Blueprint $table) {
            $table->dropColumn('comp_timbrado');
        });
    }
};
