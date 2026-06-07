<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existentes = DB::table('tipo_comprobante')
            ->whereIn('tip_comp_nombre', ['Nota de Remisión Comp', 'Nota de Remisión Vent'])
            ->pluck('tip_comp_nombre')
            ->toArray();

        if (!in_array('Nota de Remisión Comp', $existentes)) {
            DB::table('tipo_comprobante')->insert([
                'tip_comp_nombre' => 'Nota de Remisión Comp',
                'tip_comp_abrev'  => 'NRC',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        if (!in_array('Nota de Remisión Vent', $existentes)) {
            DB::table('tipo_comprobante')->insert([
                'tip_comp_nombre' => 'Nota de Remisión Vent',
                'tip_comp_abrev'  => 'NRV',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('tipo_comprobante')
            ->whereIn('tip_comp_nombre', ['Nota de Remisión Comp', 'Nota de Remisión Vent'])
            ->delete();
    }
};
