<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    private function fkExists(string $table, string $constraint): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('table_name', $table)
            ->where('constraint_name', $constraint)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }

    public function up(): void
    {
        Schema::table('entidad_adherida', function (Blueprint $table) {
            if (!$this->fkExists('entidad_adherida', 'entidad_adherida_entidad_emisora_id_foreign')) {
                $table->foreign('entidad_emisora_id')
                      ->references('id')->on('entidad_emisora')
                      ->onDelete('restrict');
            }
            if (!$this->fkExists('entidad_adherida', 'entidad_adherida_marca_tarjeta_id_foreign')) {
                $table->foreign('marca_tarjeta_id')
                      ->references('id')->on('marca_tarjeta')
                      ->onDelete('restrict');
            }
        });

        Schema::table('item_marca', function (Blueprint $table) {
            if (!$this->fkExists('item_marca', 'item_marca_item_id_foreign')) {
                $table->foreign('item_id')
                      ->references('id')->on('items')
                      ->onDelete('cascade');
            }
            if (!$this->fkExists('item_marca', 'item_marca_marca_id_foreign')) {
                $table->foreign('marca_id')
                      ->references('id')->on('marca')
                      ->onDelete('restrict');
            }
        });

        Schema::table('item_modelo', function (Blueprint $table) {
            if (!$this->fkExists('item_modelo', 'item_modelo_item_id_foreign')) {
                $table->foreign('item_id')
                      ->references('id')->on('items')
                      ->onDelete('cascade');
            }
            if (!$this->fkExists('item_modelo', 'item_modelo_modelo_id_foreign')) {
                $table->foreign('modelo_id')
                      ->references('id')->on('modelo')
                      ->onDelete('restrict');
            }
        });
    }

    public function down(): void
    {
        Schema::table('entidad_adherida', function (Blueprint $table) {
            $table->dropForeignIfExists('entidad_emisora_id');
            $table->dropForeignIfExists('marca_tarjeta_id');
        });

        Schema::table('item_marca', function (Blueprint $table) {
            $table->dropForeignIfExists('item_id');
            $table->dropForeignIfExists('marca_id');
        });

        Schema::table('item_modelo', function (Blueprint $table) {
            $table->dropForeignIfExists('item_id');
            $table->dropForeignIfExists('modelo_id');
        });
    }
};
