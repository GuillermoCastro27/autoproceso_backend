<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nota_remi_comp')) {
            Schema::table('nota_remi_comp', function (Blueprint $table) {
                if (!Schema::hasColumn('nota_remi_comp', 'chofer_nombre'))
                    $table->string('chofer_nombre', 200)->nullable()->after('sucursal_destino_id');
                if (!Schema::hasColumn('nota_remi_comp', 'chofer_documento'))
                    $table->string('chofer_documento', 20)->nullable()->after('chofer_nombre');
                if (!Schema::hasColumn('nota_remi_comp', 'chofer_telefono'))
                    $table->string('chofer_telefono', 30)->nullable()->after('chofer_documento');
                if (!Schema::hasColumn('nota_remi_comp', 'vehiculo_matricula'))
                    $table->string('vehiculo_matricula', 20)->nullable()->after('chofer_telefono');
                if (!Schema::hasColumn('nota_remi_comp', 'vehiculo_modelo'))
                    $table->string('vehiculo_modelo', 100)->nullable()->after('vehiculo_matricula');
                if (!Schema::hasColumn('nota_remi_comp', 'vehiculo_color'))
                    $table->string('vehiculo_color', 50)->nullable()->after('vehiculo_modelo');
                if (!Schema::hasColumn('nota_remi_comp', 'vehiculo_anio'))
                    $table->string('vehiculo_anio', 4)->nullable()->after('vehiculo_color');
                if (!Schema::hasColumn('nota_remi_comp', 'vehiculo_nro'))
                    $table->string('vehiculo_nro', 50)->nullable()->after('vehiculo_anio');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nota_remi_comp')) {
            Schema::table('nota_remi_comp', function (Blueprint $table) {
                $cols = ['chofer_nombre','chofer_documento','chofer_telefono',
                         'vehiculo_matricula','vehiculo_modelo','vehiculo_color',
                         'vehiculo_anio','vehiculo_nro'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('nota_remi_comp', $col))
                        $table->dropColumn($col);
                }
            });
        }
    }
};
