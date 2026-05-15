<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'ajuste_cab',
        'apertura_cierre_caja',
        'arqueo_caja',
        'cobros_cab',
        'compra_cab',
        'contrato_serv_cab',
        'descuentos_cab',
        'diagnostico_cab',
        'nota_remi_comp',
        'nota_remi_vent',
        'notas_comp_cab',
        'notas_vent_cab',
        'orden_compra_cab',
        'orden_serv_cab',
        'pedidos',
        'pedidos_ventas',
        'presupuesto_serv_cab',
        'presupuestos',
        'promociones_cab',
        'recep_cab',
        'reclamo_cli_cab',
        'solicitudes_cab',
        'ventas_cab',
    ];

    public function up(): void
    {
        // Paso 1: agregar funcionario_id nullable a cada tabla (solo si no existe)
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) continue;
            if (!Schema::hasColumn($table, 'funcionario_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('funcionario_id')->nullable();
                });
            }
        }

        // Paso 2: poblar desde users.funcionario_id (solo si user_id existe)
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) continue;
            if (Schema::hasColumn($table, 'user_id') && Schema::hasColumn($table, 'funcionario_id')) {
                DB::statement("
                    UPDATE \"{$table}\" t
                    SET funcionario_id = u.funcionario_id
                    FROM users u
                    WHERE t.user_id = u.id
                ");
            }
        }

        // Paso 3: eliminar user_id de cada tabla (solo si existe)
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) continue;
            DB::statement("ALTER TABLE \"{$table}\" DROP CONSTRAINT IF EXISTS \"{$table}_user_id_foreign\"");
            if (Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->dropColumn('user_id');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) continue;
            if (!Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('user_id')->nullable();
                });
            }

            if (Schema::hasColumn($table, 'user_id') && Schema::hasColumn($table, 'funcionario_id')) {
                DB::statement("
                    UPDATE \"{$table}\" t
                    SET user_id = u.id
                    FROM users u
                    WHERE u.funcionario_id = t.funcionario_id
                ");
            }

            if (Schema::hasColumn($table, 'funcionario_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('funcionario_id');
                });
            }
        }
    }
};
