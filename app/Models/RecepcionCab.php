<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class RecepcionCab extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'recep_cab_observaciones',
        'recep_cab_fecha',
        'recep_cab_fecha_estimada',
        'recep_cab_prioridad',
        'recep_cab_kilometraje',
        'recep_cab_nivel_combustible',
        'recep_cab_num_chasis',
        'recep_cab_fecha_salida',
        'recep_cab_estado',
        'funcionario_id',
        'clientes_id',
        'empresa_id',
        'sucursal_id',
        'tipo_servicio_id',
        'tipo_vehiculo_id',
        'solicitudes_cab_id'
    ];
    protected $table = 'recep_cab';
}
