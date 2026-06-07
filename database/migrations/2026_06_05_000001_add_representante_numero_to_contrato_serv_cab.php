<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrato_serv_cab', function (Blueprint $table) {
            if (!Schema::hasColumn('contrato_serv_cab', 'contrato_representante')) {
                $table->string('contrato_representante', 200)->nullable()->after('contrato_observacion');
            }
            if (!Schema::hasColumn('contrato_serv_cab', 'contrato_numero')) {
                $table->string('contrato_numero', 30)->nullable()->unique()->after('contrato_representante');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contrato_serv_cab', function (Blueprint $table) {
            $table->dropColumn(['contrato_representante', 'contrato_numero']);
        });
    }
};
