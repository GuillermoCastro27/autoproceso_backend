<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ajuste_det') && !Schema::hasColumn('ajuste_det', 'deposito_id')) {
            Schema::table('ajuste_det', function (Blueprint $table) {
                $table->unsignedBigInteger('deposito_id')->nullable()->after('item_id');
                $table->foreign('deposito_id')->references('id')->on('deposito');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ajuste_det') && Schema::hasColumn('ajuste_det', 'deposito_id')) {
            Schema::table('ajuste_det', function (Blueprint $table) {
                $table->dropForeign(['deposito_id']);
                $table->dropColumn('deposito_id');
            });
        }
    }
};
