<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AjusteDet extends Model
{
    use HasFactory;
    protected $table = 'ajuste_det';
    protected $fillable = [
    'ajuste_cab_id',
    'item_id', 
    'cantidad_stock', 
    'ajus_det_cantidad'
    ];
    protected $primarykey = ['ajuste_cab_id','item_id'];
    public $incrementing = false; 
}
