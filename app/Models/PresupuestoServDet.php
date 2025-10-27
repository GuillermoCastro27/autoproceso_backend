<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestoServDet extends Model
{
    use HasFactory;
    protected $fillable =[
        'presupuesto_serv_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'pres_serv_det_cantidad',
        'pres_serv_det_costo',
        'pres_serv_det_cantidad_stock'
    ];
    protected $primaryKey =['presupuesto_serv_cab_id','item_id'];
    public $incrementing = false;
    protected $table = 'presupuesto_serv_det';
}
