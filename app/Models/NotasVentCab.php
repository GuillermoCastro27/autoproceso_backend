<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotasVentCab extends Model
{
    use HasFactory;
    protected $table = 'notas_vent_cab';

    protected $fillable = [
        'nota_vent_intervalo_fecha_vence',
        'nota_vent_fecha',
        'nota_vent_estado',
        'nota_vent_cant_cuota',
        'nota_vent_tipo',
        'nota_vent_observaciones',
        'nota_vene_condicion_pago',
        'clientes_id',
        'ventas_cab_id',
        'user_id',
        'empresa_id',
        'sucursal_id'
    ];
}
