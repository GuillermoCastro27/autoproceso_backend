<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaRemiComp extends Model
{
    use HasFactory;
    protected $table = 'nota_remi_comp';
    protected $fillable =[
        'nota_remi_fecha',
        'nota_remi_observaciones',
        'nota_remi_estado',
        'user_id',
        'empresa_id',
        'sucursal_id'
        ];
}
