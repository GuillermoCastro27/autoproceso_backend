<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    protected $table = 'modelo';
    protected $fillable =[
        'modelo_nom',
        'modelo_tipo',
        'modelo_año',
        'marca_id'
    ];
    use HasFactory;
}
