<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('perfiles', 'pref_superadmin')) {
            Schema::table('perfiles', function (Blueprint $table) {
                $table->boolean('pref_superadmin')->default(false)->after('pref_descripcion');
            });
        }

        // Marcar como superadmin cualquier perfil cuya descripción contenga "admin"
        DB::table('perfiles')
            ->whereRaw("LOWER(pref_descripcion) LIKE '%admin%'")
            ->update(['pref_superadmin' => true]);
    }

    public function down(): void
    {
        Schema::table('perfiles', function (Blueprint $table) {
            $table->dropColumn('pref_superadmin');
        });
    }
};
