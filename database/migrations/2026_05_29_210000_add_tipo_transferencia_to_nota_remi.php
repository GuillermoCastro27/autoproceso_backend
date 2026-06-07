<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cabecera: tipo + sucursal destino
        if (Schema::hasTable('nota_remi_comp')) {
            Schema::table('nota_remi_comp', function (Blueprint $table) {
                if (!Schema::hasColumn('nota_remi_comp', 'tipo')) {
                    $table->string('tipo', 20)->default('PROVEEDOR')->after('nota_remi_estado');
                }
                if (!Schema::hasColumn('nota_remi_comp', 'sucursal_destino_id')) {
                    $table->unsignedBigInteger('sucursal_destino_id')->nullable()->after('tipo');
                    $table->foreign('sucursal_destino_id')->references('id')->on('sucursal');
                }
            });
        }

        // Detalle: deposito_id (origen) + deposito_destino_id
        if (Schema::hasTable('nota_remi_com_det')) {
            Schema::table('nota_remi_com_det', function (Blueprint $table) {
                if (!Schema::hasColumn('nota_remi_com_det', 'deposito_id')) {
                    $table->unsignedBigInteger('deposito_id')->nullable()->after('nota_remi_com_det_cantidad');
                    $table->foreign('deposito_id')->references('id')->on('deposito');
                }
                if (!Schema::hasColumn('nota_remi_com_det', 'deposito_destino_id')) {
                    $table->unsignedBigInteger('deposito_destino_id')->nullable()->after('deposito_id');
                    $table->foreign('deposito_destino_id')->references('id')->on('deposito');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nota_remi_com_det')) {
            Schema::table('nota_remi_com_det', function (Blueprint $table) {
                if (Schema::hasColumn('nota_remi_com_det', 'deposito_destino_id')) {
                    $table->dropForeign(['deposito_destino_id']);
                    $table->dropColumn('deposito_destino_id');
                }
                if (Schema::hasColumn('nota_remi_com_det', 'deposito_id')) {
                    $table->dropForeign(['deposito_id']);
                    $table->dropColumn('deposito_id');
                }
            });
        }
        if (Schema::hasTable('nota_remi_comp')) {
            Schema::table('nota_remi_comp', function (Blueprint $table) {
                if (Schema::hasColumn('nota_remi_comp', 'sucursal_destino_id')) {
                    $table->dropForeign(['sucursal_destino_id']);
                    $table->dropColumn('sucursal_destino_id');
                }
                if (Schema::hasColumn('nota_remi_comp', 'tipo')) {
                    $table->dropColumn('tipo');
                }
            });
        }
    }
};
