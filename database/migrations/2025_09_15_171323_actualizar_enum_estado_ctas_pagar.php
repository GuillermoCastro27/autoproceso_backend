<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Aseguramos que la columna sea VARCHAR para poder modificar el check
        DB::statement("ALTER TABLE ctas_pagar ALTER COLUMN cta_pag_estado TYPE VARCHAR(20)");

        // Eliminamos cualquier check constraint anterior
        DB::statement("
            ALTER TABLE ctas_pagar
            DROP CONSTRAINT IF EXISTS ctas_pagar_cta_pag_estado_check
        ");

        // Creamos el nuevo check con los 4 estados
        DB::statement("
            ALTER TABLE ctas_pagar
            ADD CONSTRAINT ctas_pagar_cta_pag_estado_check
            CHECK (cta_pag_estado IN ('Pendiente','Pagada','Vencida','Anulado'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Volvemos al check anterior (sin Anulado)
        DB::statement("
            ALTER TABLE ctas_pagar
            DROP CONSTRAINT IF EXISTS ctas_pagar_cta_pag_estado_check
        ");

        DB::statement("
            ALTER TABLE ctas_pagar
            ADD CONSTRAINT ctas_pagar_cta_pag_estado_check
            CHECK (cta_pag_estado IN ('Pendiente','Pagada','Vencida'))
        ");
    }
};
