<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    use HasFactory;
    protected $fillable =[
        'pre_observaciones',
        'pre_estado',
        'pre_fecha',
        'pre_vence',
        'proveedor_id',
        'pedido_id',
        'user_id',
        'empresa_id',
        'sucursal_id'
    ];
}
