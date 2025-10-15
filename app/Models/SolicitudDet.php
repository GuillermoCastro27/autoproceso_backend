<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudDet extends Model
{
    use HasFactory;
    protected $fillable =[
        'solicitudes_cab_id',
        'item_id','tipo_impuesto_id',
        'soli_det_cantidad',
        'soli_det_costo',
        'soli_det_cantidad_stock'
    ];
    protected $primaryKey =[
        'solicitudes_cab_id',
        'item_id'];
    public $incrementing = false;
    protected $table = 'solicitudes_det';
}
