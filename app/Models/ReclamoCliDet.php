<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReclamoCliDet extends Model
{
    use HasFactory;
    protected $fillable =[
        'reclamo_cli_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'rec_cli_det_cantidad',
        'rec_cli_det_costo',
        'rec_cli_det_cantidad_stock'
    ];
    protected $primaryKey =[
        'reclamo_cli_cab_id',
        'item_id'];
    public $incrementing = false;
    protected $table = 'reclamo_cli_det';
}
