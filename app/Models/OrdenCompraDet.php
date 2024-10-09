<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenCompraDet extends Model
{
    use HasFactory;
    protected $fillable =['orden_compra_cab_id','item_id','tipo_impuesto_id','orden_compra_det_cantidad','orden_compra_det_costo'];
    protected $primaryKey =['orden_compra_cab_id','item_id'];
    public $incrementing = false;
    protected $table = 'orden_compra_det';
}
