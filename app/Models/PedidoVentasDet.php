<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoVentasDet extends Model
{
    use HasFactory;

    protected $table = 'pedidos_ventas_det';

    protected $fillable = [
        'pedidos_ventas_id',
        'item_id',
        'det_cantidad',
        'cantidad_stock'
    ];

    public $incrementing = false; // clave compuesta
    protected $keyType = 'int';

    public function getKeyName()
    {
        return null;
    }

    public function getIncrementing()
    {
        return false;
    }
}
