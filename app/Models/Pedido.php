<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    protected $fillable =[
    'ped_vence',
    'ped_fecha',
    'ped_pbservaciones',
    'ped_estado',
    'user_id',
    'empresa_id',
    'sucursal_id'
    ];
}
