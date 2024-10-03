<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $fillable =[
    'item_decripcion',
    'item_costo',
    'item_precio',
    'tipo_id',
    'tipo_impuesto_id',
    'marca_id',
    'modelo_id'
    ];
}
