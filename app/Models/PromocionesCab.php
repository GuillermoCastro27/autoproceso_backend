<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromocionesCab extends Model
{
    use HasFactory;
    protected $fillable = [
        'prom_cab_observaciones',
        'prom_cab_nombre',
        'prom_cab_fecha_registro',
        'prom_cab_fecha_inicio',
        'prom_cab_fecha_fin',
        'prom_cab_estado',
        'user_id',
        'empresa_id',
        'tipo_promociones_id',
        'sucursal_id'
    ];
    protected $table = 'promociones_cab';
}
