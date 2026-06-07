<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tipo_comprobante')) {
            Schema::create('tipo_comprobante', function (Blueprint $table) {
                $table->id();
                $table->string('tip_comp_nombre', 100);
                $table->string('tip_comp_abrev', 10);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('timbrado')) {
            Schema::create('timbrado', function (Blueprint $table) {
                $table->id();
                $table->string('tim_numero', 20);
                $table->date('tim_fecha_inicio');
                $table->date('tim_fecha_fin');
                $table->unsignedInteger('tim_nro_desde');
                $table->unsignedInteger('tim_nro_hasta');
                $table->unsignedInteger('tim_nro_actual')->default(0);
                $table->string('tim_estado', 20)->default('activo');
                $table->foreignId('tipo_comprobante_id')->constrained('tipo_comprobante');
                $table->foreignId('empresa_id')->constrained('empresa');
                $table->foreignId('sucursal_id')->constrained('sucursal');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('ventas_cab')) {
            Schema::table('ventas_cab', function (Blueprint $table) {
                if (!Schema::hasColumn('ventas_cab', 'timbrado_id')) {
                    $table->foreignId('timbrado_id')->nullable()->constrained('timbrado');
                }
                if (!Schema::hasColumn('ventas_cab', 'vent_nro_comprobante')) {
                    $table->unsignedInteger('vent_nro_comprobante')->nullable();
                }
            });
        }

        if (Schema::hasTable('notas_vent_cab')) {
            Schema::table('notas_vent_cab', function (Blueprint $table) {
                if (!Schema::hasColumn('notas_vent_cab', 'timbrado_id')) {
                    $table->foreignId('timbrado_id')->nullable()->constrained('timbrado');
                }
                if (!Schema::hasColumn('notas_vent_cab', 'nota_vent_nro_comprobante')) {
                    $table->unsignedInteger('nota_vent_nro_comprobante')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notas_vent_cab')) {
            Schema::table('notas_vent_cab', function (Blueprint $table) {
                if (Schema::hasColumn('notas_vent_cab', 'timbrado_id')) {
                    $table->dropForeign(['timbrado_id']);
                    $table->dropColumn('timbrado_id');
                }
                if (Schema::hasColumn('notas_vent_cab', 'nota_vent_nro_comprobante')) {
                    $table->dropColumn('nota_vent_nro_comprobante');
                }
            });
        }

        if (Schema::hasTable('ventas_cab')) {
            Schema::table('ventas_cab', function (Blueprint $table) {
                if (Schema::hasColumn('ventas_cab', 'timbrado_id')) {
                    $table->dropForeign(['timbrado_id']);
                    $table->dropColumn('timbrado_id');
                }
                if (Schema::hasColumn('ventas_cab', 'vent_nro_comprobante')) {
                    $table->dropColumn('vent_nro_comprobante');
                }
            });
        }

        Schema::dropIfExists('timbrado');
        Schema::dropIfExists('tipo_comprobante');
    }
};
