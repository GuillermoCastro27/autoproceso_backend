<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accesos', function (Blueprint $table) {
            $table->foreignId('mod_id')->nullable()->after('perfil_id')
            $table->timestamp('acc_fecha')->nullable()->after('acc_estado');
        });
    }

    public function down(): void
    {
        Schema::table('accesos', function (Blueprint $table) {
            $table->dropColumn(['mod_id', 'acc_fecha']);
        });
    }
};
