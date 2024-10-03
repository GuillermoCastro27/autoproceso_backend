<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestosDetalle extends Model
{
    use HasFactory;
    protected $fillable =['presupuesto_id','item_id','det_costo','det_cantidad'];
    protected $primaryKey =['presupuesto_id','item_id'];
    public $incrementing = false;
}
