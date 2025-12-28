<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CobrosCab extends Model
{
    use HasFactory;
    protected $table = 'cobros_cab';
    protected $fillable = [
        'cobro_fecha',
        'cobro_estado',
        'cobro_importe',
        'cobro_observacion',
        'numero_documento',
        'nro_voucher',
        'portador',
        'fecha_cobro_diferido',
        'forma_cobro_id',
        'clientes_id',
        'ventas_cab_id',
        'user_id',
        'caja_id',
        'empresa_id',
        'sucursal_id',
        'apertura_cierre_caja_id',
        'entidad_emisora_id',
        'marca_tarjeta_id',
        'entidad_adherida_id'
    ];
}
