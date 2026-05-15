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
        Schema::table('reclamo_cli_cab', function (Blueprint $table) {
            if (!Schema::hasColumn('reclamo_cli_cab', 'token_seguimiento')) {
                $table->string('token_seguimiento', 64)->unique()->nullable()->after('venta_cab_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reclamo_cli_cab', function (Blueprint $table) {
            if (Schema::hasColumn('reclamo_cli_cab', 'token_seguimiento')) $table->dropColumn('token_seguimiento');
        });
    }
};
