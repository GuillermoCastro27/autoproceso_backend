<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tipos = [
            ['tipo_descripcion' => 'INSUMO',    'tipo_objeto' => 'ITEMS', 'tipo_estado' => 'activo'],
            ['tipo_descripcion' => 'REPUESTO',  'tipo_objeto' => 'ITEMS', 'tipo_estado' => 'activo'],
            ['tipo_descripcion' => 'SERVICIO',  'tipo_objeto' => 'ITEMS', 'tipo_estado' => 'activo'],
        ];

        foreach ($tipos as $tipo) {
            $existe = DB::table('tipos')
                ->whereRaw('LOWER(tipo_descripcion) = LOWER(?)', [$tipo['tipo_descripcion']])
                ->where('tipo_objeto', 'ITEMS')
                ->exists();

            if (!$existe) {
                DB::table('tipos')->insert(array_merge($tipo, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('tipos')
            ->whereIn('tipo_descripcion', ['INSUMO', 'REPUESTO', 'SERVICIO'])
            ->where('tipo_objeto', 'ITEMS')
            ->delete();
    }
};
