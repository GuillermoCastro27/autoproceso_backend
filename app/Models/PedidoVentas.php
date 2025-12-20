<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoVentas extends Model
{
    use HasFactory;
    protected $fillable =[
    'ped_ven_fecha',
    'ped_ven_vence',
    'ped_ven_observaciones',
    'ped_ven_estado',
    'user_id',
    'empresa_id',
    'sucursal_id',
    'clientes_id'
    ];
    protected $table = 'pedidos_ventas';
}
