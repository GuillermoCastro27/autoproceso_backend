<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsumosCab extends Model
{
    protected $table    = 'insumos_cab';
    protected $fillable = ['orden_serv_cab_id', 'ins_cab_fecha_registro', 'ins_cab_estado'];
}
