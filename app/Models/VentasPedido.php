<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentasPedido extends Model
{
    protected $table = 'ventas_pedidos';

    protected $fillable = [
        'ventas_cab_id',
        'pedidos_ventas_id',
    ];
}
