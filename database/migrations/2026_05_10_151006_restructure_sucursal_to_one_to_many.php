<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Tablas con FK hacia sucursal.empresa_id (old PK)
    private array $fkTables = [
        'ajuste_cab', 'apertura_cierre_caja', 'cobros_cab', 'compra_cab',
        'contrato_serv_cab', 'descuentos_cab', 'diagnostico_cab',
        'nota_remi_comp', 'nota_remi_vent', 'notas_comp_cab', 'notas_vent_cab',
        'orden_compra_cab', 'orden_serv_cab', 'pedidos', 'pedidos_ventas',
        'presupuesto_serv_cab', 'presupuestos', 'promociones_cab', 'recep_cab',
        'reclamo_cli_cab', 'solicitudes_cab', 'ventas_cab', 'deposito',
    ];

    // Tablas adicionales con sucursal_id pero sin FK declarada
    private array $extraTables = [];

    public function up(): void
    {
        // 1. Eliminar FKs existentes (si existen)
        foreach ($this->fkTables as $table) {
            if (!Schema::hasTable($table)) continue;
            DB::statement("ALTER TABLE \"{$table}\" DROP CONSTRAINT IF EXISTS \"{$table}_sucursal_id_foreign\"");
        }

        // 2. Quitar el PK de empresa_id
        DB::statement('ALTER TABLE sucursal DROP CONSTRAINT IF EXISTS sucursal_pkey');

        // 3. Agregar nueva columna id BIGSERIAL como nueva PK (solo si no existe)
        if (!Schema::hasColumn('sucursal', 'id')) {
            DB::statement('ALTER TABLE sucursal ADD COLUMN id BIGSERIAL');
            DB::statement('ALTER TABLE sucursal ADD PRIMARY KEY (id)');
        }

        // 4. Actualizar sucursal_id en todas las tablas (solo si la columna existe)
        $allTables = array_merge($this->fkTables, $this->extraTables);
        foreach ($allTables as $table) {
            if (!Schema::hasTable($table)) continue;
            if (!Schema::hasColumn($table, 'sucursal_id')) continue;
            DB::statement(
                "UPDATE \"{$table}\" t SET sucursal_id = s.id FROM sucursal s WHERE s.empresa_id = t.sucursal_id"
            );
        }

        // 5. FKs no se recrean (eliminadas globalmente para compatibilidad con migración fresca)
    }

    public function down(): void
    {
        // 1. Eliminar FK del nuevo PK
        foreach ($this->fkTables as $table) {
            DB::statement("ALTER TABLE \"{$table}\" DROP CONSTRAINT IF EXISTS \"{$table}_sucursal_id_foreign\"");
        }

        // 2. Revertir sucursal_id a los valores de empresa_id
        $allTables = array_merge($this->fkTables, $this->extraTables);
        foreach ($allTables as $table) {
            DB::statement(
                "UPDATE \"{$table}\" t SET sucursal_id = s.empresa_id FROM sucursal s WHERE s.id = t.sucursal_id"
            );
        }

        // 3. Quitar la columna id y restaurar empresa_id como PK
        DB::statement('ALTER TABLE sucursal DROP COLUMN id');
        DB::statement('ALTER TABLE sucursal ADD PRIMARY KEY (empresa_id)');

        // 4. Restaurar FK apuntando al old PK (empresa_id)
        foreach ($this->fkTables as $table) {
            DB::statement(
                "ALTER TABLE \"{$table}\" ADD CONSTRAINT \"{$table}_sucursal_id_foreign\" FOREIGN KEY (sucursal_id) REFERENCES sucursal(empresa_id)"
            );
        }
    }
};
