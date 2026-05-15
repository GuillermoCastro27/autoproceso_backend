<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenServVenta extends Model
{
    use HasFactory;

    protected $table = 'orden_serv_venta';

    protected $fillable = [
        'ventas_cab_id',
        'orden_serv_cab_id',
        'contrato_serv_cab_id',
    ];
}
