<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraDet extends Model
{
    use HasFactory;
    protected $fillable =['compra_cab_id','item_id','tipo_impuesto_id','comp_det_cantidad','comp_det_costo'];
    protected $primaryKey =['compra_cab_id','item_id'];
    public $incrementing = false;
    protected $table = 'compra_det';
}
