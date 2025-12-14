<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosticoCab extends Model
{
    use HasFactory;
     protected $fillable = [
        'diag_cab_observaciones',
        'diag_cab_fecha',
        'diag_cab_estado',
        'diag_cab_prioridad',
        'diag_cab_kilometraje',
        'diag_cab_nivel_combustible',
        'user_id',
        'clientes_id',
        'empresa_id',
        'sucursal_id',
        'tipo_servicio_id',
        'tipo_diagnostico_id',
        'recep_cab_id',
        'tipo_vehiculo_id'
    ];
    protected $table = 'diagnostico_cab';
}
