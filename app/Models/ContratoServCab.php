<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratoServCab extends Model
{
    use HasFactory;
    protected $table = 'contrato_serv_cab';

    protected $fillable = [
        'contrato_fecha',
        'contrato_fecha_inicio',
        'contrato_fecha_fin',
        'contrato_intervalo_fecha_vence',
        'contrato_estado',
        'contrato_condicion_pago',
        'contrato_cuotas',
        'contrato_observacion',
        'contrato_archivo_url',
        'empresa_id',
        'sucursal_id',
        'clientes_id',
        'tipo_servicio_id',
        'user_id'
    ];

}
