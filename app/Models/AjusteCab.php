<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AjusteCab extends Model
{
    protected $table = 'ajuste_cab';
    use HasFactory;
    protected $fillable = [
        'ajus_cab_fecha',
        'ajus_cab_estado', 
        'user_id', 
        'empresa_id', 
        'sucursal_id', 
        'motivo_ajuste_id'
        ];
}
