<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ahora = now();

        // tipo_comprobante: 2=Nota de Crédito, 3=Nota de Débito, 5=Nota de Remisión Vent
        // empresa_id=6, sucursales 1 y 2

        $timbrados = [
            ['tipo_comprobante_id' => 2, 'empresa_id' => 6, 'sucursal_id' => 1, 'tim_numero' => '22222201', 'tim_punto_expedicion' => '001'],
            ['tipo_comprobante_id' => 2, 'empresa_id' => 6, 'sucursal_id' => 2, 'tim_numero' => '22222202', 'tim_punto_expedicion' => '002'],
            ['tipo_comprobante_id' => 3, 'empresa_id' => 6, 'sucursal_id' => 1, 'tim_numero' => '33333301', 'tim_punto_expedicion' => '001'],
            ['tipo_comprobante_id' => 3, 'empresa_id' => 6, 'sucursal_id' => 2, 'tim_numero' => '33333302', 'tim_punto_expedicion' => '002'],
            ['tipo_comprobante_id' => 5, 'empresa_id' => 6, 'sucursal_id' => 1, 'tim_numero' => '55555501', 'tim_punto_expedicion' => '001'],
            ['tipo_comprobante_id' => 5, 'empresa_id' => 6, 'sucursal_id' => 2, 'tim_numero' => '55555502', 'tim_punto_expedicion' => '002'],
        ];

        foreach ($timbrados as $t) {
            $existe = DB::table('timbrado')
                ->where('tipo_comprobante_id', $t['tipo_comprobante_id'])
                ->where('empresa_id',  $t['empresa_id'])
                ->where('sucursal_id', $t['sucursal_id'])
                ->where('tim_estado',  'activo')
                ->exists();

            if (!$existe) {
                DB::table('timbrado')->insert([
                    'tipo_comprobante_id'  => $t['tipo_comprobante_id'],
                    'empresa_id'           => $t['empresa_id'],
                    'sucursal_id'          => $t['sucursal_id'],
                    'tim_numero'           => $t['tim_numero'],
                    'tim_fecha_inicio'     => '2026-01-01',
                    'tim_fecha_fin'        => '2027-12-31',
                    'tim_nro_desde'        => 1,
                    'tim_nro_hasta'        => 9999999,
                    'tim_nro_actual'       => 0,
                    'tim_estado'           => 'activo',
                    'tim_establecimiento'  => '001',
                    'tim_punto_expedicion' => $t['tim_punto_expedicion'],
                    'created_at'           => $ahora,
                    'updated_at'           => $ahora,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('timbrado')->whereIn('tim_numero', [
            '22222201', '22222202',
            '33333301', '33333302',
            '55555501', '55555502',
        ])->delete();
    }
};
