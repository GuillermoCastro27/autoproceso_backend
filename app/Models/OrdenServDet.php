<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenServDet extends Model
{
    use HasFactory;
    protected $fillable =[
        'orden_serv_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'orden_serv_det_cantidad',
        'orden_serv_det_costo',
        'orden_serv_det_cantidad_stock'
    ];
    protected $primaryKey =['orden_serv_cab_id','item_id'];
    public $incrementing = false;
    protected $table = 'orden_serv_det';
}
