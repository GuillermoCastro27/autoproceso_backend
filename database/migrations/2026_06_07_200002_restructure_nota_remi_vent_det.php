<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('nota_remi_vent_det')) return;

        // Agregar precio si no existe (era faltante en la migration original)
        if (!Schema::hasColumn('nota_remi_vent_det', 'nota_remi_vent_det_precio')) {
            DB::statement('ALTER TABLE nota_remi_vent_det ADD COLUMN nota_remi_vent_det_precio NUMERIC(15,2) NULL DEFAULT 0');
        }

        // Agregar tipo_origen para distinguir entre ítems de venta e insumos de servicio
        if (!Schema::hasColumn('nota_remi_vent_det', 'tipo_origen')) {
            DB::statement("ALTER TABLE nota_remi_vent_det ADD COLUMN tipo_origen VARCHAR(10) NOT NULL DEFAULT 'venta'");
        }

        // Agregar orden_serv_cab_id para insumos de órdenes de servicio
        if (!Schema::hasColumn('nota_remi_vent_det', 'orden_serv_cab_id')) {
            DB::statement('ALTER TABLE nota_remi_vent_det ADD COLUMN orden_serv_cab_id BIGINT NULL');
        }

        // Cambiar la PK compuesta a un id autoincremental para permitir el mismo item_id
        // desde distintos orígenes (venta + servicio)
        $hasPk = DB::select("
            SELECT constraint_name FROM information_schema.table_constraints
            WHERE table_name = 'nota_remi_vent_det'
              AND constraint_type = 'PRIMARY KEY'
        ");

        if (!empty($hasPk)) {
            $pkName = $hasPk[0]->constraint_name;
            DB::statement("ALTER TABLE nota_remi_vent_det DROP CONSTRAINT \"{$pkName}\"");
        }

        if (!Schema::hasColumn('nota_remi_vent_det', 'id')) {
            DB::statement('ALTER TABLE nota_remi_vent_det ADD COLUMN id BIGSERIAL');
            DB::statement('ALTER TABLE nota_remi_vent_det ADD PRIMARY KEY (id)');
        }
    }

    public function down(): void {}
};
