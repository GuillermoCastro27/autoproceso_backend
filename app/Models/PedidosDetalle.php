<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidosDetalle extends Model
{
    use HasFactory;

    protected $fillable = ['pedidos_id', 'item_id', 'det_cantidad', 'cantidad_stock'];

    public $incrementing = false; // No es autoincremental porque es clave compuesta
    protected $keyType = 'int'; // Asegura que Laravel trate los IDs como enteros

    public function getKeyName()
    {
        return null; // Laravel no debe asumir una clave primaria única
    }

    public function getIncrementing()
    {
        return false;
    }
}

