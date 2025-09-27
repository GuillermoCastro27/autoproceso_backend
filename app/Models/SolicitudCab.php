<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudCab extends Model
{
    use HasFactory;
    protected $fillable = [
        'soli_cab_observaciones',
        'soli_cab_fecha',
        'soli_cab_fecha_estimada',
        'soli_cab_prioridad',
        'soli_cab_estado',
        'user_id',
        'clientes_id',
        'empresa_id',
        'tipo_servicio_id',
        'sucursal_id'
    ];
    protected $table = 'solicitudes_cab';
}
