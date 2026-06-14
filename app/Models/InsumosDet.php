<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsumosDet extends Model
{
    protected $table    = 'insumos_det';
    protected $fillable = [
        'insumos_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'ins_det_cantidad',
        'ins_det_costo',
        'marca_id',
        'modelo_id',
    ];
}
