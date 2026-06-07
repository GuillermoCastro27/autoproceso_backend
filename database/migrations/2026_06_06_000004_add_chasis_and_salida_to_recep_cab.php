<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recep_cab', function (Blueprint $table) {
            if (!Schema::hasColumn('recep_cab', 'recep_cab_num_chasis')) {
                $table->string('recep_cab_num_chasis', 30)->nullable()->after('recep_cab_nivel_combustible');
            }
            if (!Schema::hasColumn('recep_cab', 'recep_cab_fecha_salida')) {
                $table->timestamp('recep_cab_fecha_salida')->nullable()->after('recep_cab_num_chasis');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recep_cab', function (Blueprint $table) {
            $table->dropColumn(['recep_cab_num_chasis', 'recep_cab_fecha_salida']);
        });
    }
};
