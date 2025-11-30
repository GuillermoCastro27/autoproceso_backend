<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecepcionCab extends Model
{
    use HasFactory;
    protected $fillable = [
        'recep_cab_observaciones',
        'recep_cab_fecha',
        'recep_cab_fecha_estimada',
        'recep_cab_prioridad',
        'recep_cab_kilometraje',
        'recep_cab_nivel_combustible',
        'recep_cab_estado',
        'user_id',
        'clientes_id',
        'empresa_id',
        'sucursal_id',
        'tipo_servicio_id',
        'tipo_vehiculo_id',
        'solicitudes_cab_id'
    ];
    protected $table = 'recep_cab';
}
