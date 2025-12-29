<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaRemiVentDet extends Model
{
    use HasFactory;
    protected $table = 'nota_remi_vent_det';
    protected $fillable = [
    'nota_remi_vent_id',
    'item_id', 
    'nota_remi_vent_det_cantidad',
    'nota_remi_vent_det_precio'
    ];
    protected $primarykey = ['nota_remi_vent_id','item_id'];
    public $incrementing = false; 
}
