<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('apertura_cierre_caja', function (Blueprint $table) {
            if (Schema::hasColumn('apertura_cierre_caja', 'user_cierre_id')) {
                if (Schema::hasColumn('apertura_cierre_caja', 'user_cierre_id')) $table->dropColumn('user_cierre_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('apertura_cierre_caja', function (Blueprint $table) {
            if (!Schema::hasColumn('apertura_cierre_caja', 'user_cierre_id')) $table->unsignedBigInteger('user_cierre_id')->nullable();

        });
    }
};
