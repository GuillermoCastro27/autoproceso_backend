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
        Schema::table('solicitudes_det', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes_det', 'deposito_id')) $table->dropColumn('deposito_id');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_det', function (Blueprint $table) {
            if (!Schema::hasColumn('solicitudes_det', 'deposito_id')) $table->unsignedBigInteger('deposito_id')->nullable()->after('item_id');
        });
    }
};
