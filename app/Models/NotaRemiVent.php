<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaRemiVent extends Model
{
    use HasFactory;
    protected $table = 'nota_remi_vent';
    protected $fillable =[
        'nota_remi_vent_fecha',
        'nota_remi_vent_observaciones',
        'nota_remi_vent_estado',
        'user_id',
        'empresa_id',
        'sucursal_id',
        'ventas_cab_id',
        'clientes_id'
    ];
}
