<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromocionesDet extends Model
{
    use HasFactory;
    protected $fillable =[
        'promociones_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'prom_det_cantidad',
        'prom_det_costo',
    ];
    protected $primaryKey =[
        'promociones_cab_id',
        'item_id'];
    public $incrementing = false;
    protected $table = 'promociones_det';
}
