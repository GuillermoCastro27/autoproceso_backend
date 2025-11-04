<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratoServDet extends Model
{
    use HasFactory;
    protected $fillable =[
        'contrato_serv_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'contrato_serv_det_cantidad',
        'contrato_serv_det_costo',
        'contrato_serv_det_cantidad_stock'
    ];
    protected $primaryKey =[
        'contrato_serv_cab_id',
        'item_id'];
    public $incrementing = false;
    protected $table = 'contrato_serv_det';
}
