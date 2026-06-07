<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('timbrado') && !Schema::hasColumn('timbrado', 'tim_establecimiento')) {
            Schema::table('timbrado', function (Blueprint $table) {
                $table->string('tim_establecimiento', 3)->default('001')->after('tim_numero');
                $table->string('tim_punto_expedicion', 3)->default('001')->after('tim_establecimiento');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('timbrado')) {
            Schema::table('timbrado', function (Blueprint $table) {
                if (Schema::hasColumn('timbrado', 'tim_establecimiento'))
                    $table->dropColumn('tim_establecimiento');
                if (Schema::hasColumn('timbrado', 'tim_punto_expedicion'))
                    $table->dropColumn('tim_punto_expedicion');
            });
        }
    }
};
