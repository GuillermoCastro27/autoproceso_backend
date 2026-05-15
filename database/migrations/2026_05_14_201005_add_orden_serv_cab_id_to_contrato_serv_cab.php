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
            if (!Schema::hasColumn('contrato_serv_cab', 'orden_serv_cab_id')) $table->unsignedBigInteger('orden_serv_cab_id')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('contrato_serv_cab', function (Blueprint $table) {
            $table->dropColumn('orden_serv_cab_id');
        });
    }
};
