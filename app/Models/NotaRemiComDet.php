<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaRemiComDet extends Model
{
    use HasFactory;
    protected $table = 'nota_remi_com_det';
    protected $fillable = [
    'nota_remi_comp_id',
    'item_id', 
    'nota_remi_com_det_cantidad'
    ];
    protected $primarykey = ['nota_remi_comp_id','item_id'];
    public $incrementing = false; 
}
