<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecepcionDet extends Model
{
    use HasFactory;
    protected $fillable =[
        'recep_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'recep_det_cantidad',
        'recep_det_costo',
        'recep_det_cantidad_stock'
    ];
    protected $primaryKey =[
        'recep_cab_id',
        'item_id'];
    public $incrementing = false;
    protected $table = 'recep_det';
}
