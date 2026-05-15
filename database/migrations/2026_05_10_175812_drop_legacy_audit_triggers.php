<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_aud_pedidos ON pedidos;');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_aud_pedidos_detalles ON pedidos_detalles;');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_auditoria_general();');
    }

    public function down(): void
    {
        // No se restauran los triggers del sistema de auditoría legacy
    }
};
