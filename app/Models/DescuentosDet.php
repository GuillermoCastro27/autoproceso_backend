<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DescuentosDet extends Model
{
    use HasFactory;
    protected $fillable =[
        'descuentos_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'desc_det_cantidad',
        'desc_det_costo',
    ];
    protected $primaryKey =[
        'descuentos_cab_id',
        'item_id'];
    public $incrementing = false;
    protected $table = 'descuentos_det';
}
