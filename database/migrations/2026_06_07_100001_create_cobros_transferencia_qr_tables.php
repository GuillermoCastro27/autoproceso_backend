<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        if (!Schema::hasTable('cobros_transferencia')) {
            Schema::create('cobros_transferencia', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cobros_cab_id');
                $table->string('banco_entidad', 100)->nullable();
                $table->string('nro_referencia', 100)->nullable();
                $table->decimal('monto_transferencia', 15, 2);
                $table->timestamps();
                $table->foreign('cobros_cab_id')->references('id')->on('cobros_cab')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('cobros_qr')) {
            Schema::create('cobros_qr', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cobros_cab_id');
                $table->string('nro_referencia', 100)->nullable();
                $table->decimal('monto_qr', 15, 2);
                $table->timestamps();
                $table->foreign('cobros_cab_id')->references('id')->on('cobros_cab')->onDelete('cascade');
            });
        }

        // Insertar TRANSFERENCIA en forma_cobro si no existe
        DB::statement("
            INSERT INTO forma_cobro (for_cob_descripcion, created_at, updated_at)
            SELECT 'TRANSFERENCIA', NOW(), NOW()
            WHERE NOT EXISTS (
                SELECT 1 FROM forma_cobro WHERE UPPER(for_cob_descripcion) = 'TRANSFERENCIA'
            )
        ");

        // Insertar QR en forma_cobro si no existe
        DB::statement("
            INSERT INTO forma_cobro (for_cob_descripcion, created_at, updated_at)
            SELECT 'QR', NOW(), NOW()
            WHERE NOT EXISTS (
                SELECT 1 FROM forma_cobro WHERE UPPER(for_cob_descripcion) = 'QR'
            )
        ");
    }

    public function down()
    {
        Schema::dropIfExists('cobros_qr');
        Schema::dropIfExists('cobros_transferencia');
        DB::table('forma_cobro')->whereIn('for_cob_descripcion', ['TRANSFERENCIA', 'QR'])->delete();
    }
};
