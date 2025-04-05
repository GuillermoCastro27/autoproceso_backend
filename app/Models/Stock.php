<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stock'; // Nombre de la tabla
    protected $primaryKey = 'stock_id'; // Definir la clave primaria

    public $incrementing = true; // Indicar que es autoincremental (si aplica)
    protected $keyType = 'int'; // Laravel usa 'int' incluso para bigint

    protected $fillable = ['item_id', 'cantidad', 'created_at', 'updated_at'];

    public function pedidosDetalles()
    {
        return $this->hasMany(PedidosDetalle::class, 'stock_id', 'stock_id');
    }
}
