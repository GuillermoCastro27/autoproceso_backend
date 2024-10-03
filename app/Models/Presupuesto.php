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
        'pre_vence',
        'proveedor_id',
        'pedido_id',
        'user_id'
    ];
}
