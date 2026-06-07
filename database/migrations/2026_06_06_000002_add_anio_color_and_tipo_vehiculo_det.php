<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_vehiculo', function (Blueprint $table) {
            if (!Schema::hasColumn('tipo_vehiculo', 'tv_anio')) {
                $table->integer('tv_anio')->nullable()->after('tip_veh_observacion');
            }
            if (!Schema::hasColumn('tipo_vehiculo', 'tv_color')) {
                $table->string('tv_color', 30)->nullable()->after('tv_anio');
            }
        });

        if (!Schema::hasTable('tipo_vehiculo_det')) {
            Schema::create('tipo_vehiculo_det', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tipo_vehiculo_id');
                $table->string('tv_det_placa', 20)->nullable();
                $table->string('tv_det_num_chasis', 50)->nullable();
                $table->string('tv_det_num_motor', 50)->nullable();
                $table->timestamps();
                $table->foreign('tipo_vehiculo_id')
                      ->references('id')->on('tipo_vehiculo')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_vehiculo_det');

        Schema::table('tipo_vehiculo', function (Blueprint $table) {
            if (Schema::hasColumn('tipo_vehiculo', 'tv_color')) {
                $table->dropColumn('tv_color');
            }
            if (Schema::hasColumn('tipo_vehiculo', 'tv_anio')) {
                $table->dropColumn('tv_anio');
            }
        });
    }
};
