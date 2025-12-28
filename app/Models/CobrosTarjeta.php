<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CobrosTarjeta extends Model
{
    use HasFactory;
    protected $table = 'cobros_tarjeta';

    protected $fillable = [
        'cobros_cab_id',
        'entidad_emisora_tarjeta_id',
        'marca_tarjeta_tarjeta_id',
        'entidad_adherida_tarjeta_id',
        'nro_tarjeta',
        'fecha_vencimiento',
        'nro_voucher',
        'monto_tarjeta'
    ];
}
