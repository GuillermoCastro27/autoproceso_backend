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
        Schema::table('contrato_serv_cab', function (Blueprint $table) {
            if (!Schema::hasColumn('contrato_serv_cab', 'contrato_tipo')) $table->string('contrato_tipo', 30)->nullable()->after('contrato_condicion_pago');
            if (!Schema::hasColumn('contrato_serv_cab', 'contrato_objeto')) $table->text('contrato_objeto')->nullable()->after('contrato_tipo');
            if (!Schema::hasColumn('contrato_serv_cab', 'contrato_alcance')) $table->text('contrato_alcance')->nullable()->after('contrato_objeto');
            if (!Schema::hasColumn('contrato_serv_cab', 'contrato_responsabilidad')) $table->text('contrato_responsabilidad')->nullable()->after('contrato_alcance');
            if (!Schema::hasColumn('contrato_serv_cab', 'contrato_garantia')) $table->text('contrato_garantia') ->nullable()->after('contrato_responsabilidad');
            if (!Schema::hasColumn('contrato_serv_cab', 'contrato_limitacion')) $table->text('contrato_limitacion') ->nullable() ->after('contrato_garantia');
            if (!Schema::hasColumn('contrato_serv_cab', 'contrato_fuerza_mayor')) $table->text('contrato_fuerza_mayor')->nullable()->after('contrato_limitacion');
            if (!Schema::hasColumn('contrato_serv_cab', 'contrato_jurisdiccion')) $table->text('contrato_jurisdiccion')->nullable()->after('contrato_fuerza_mayor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contrato_serv_cab', function (Blueprint $table) {
            //
        });
    }
};
