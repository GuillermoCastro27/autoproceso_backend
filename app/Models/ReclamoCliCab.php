<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReclamoCliCab extends Model
{
    use HasFactory;
    protected $fillable = [
        'rec_cli_cab_fecha',
        'rec_cli_cab_fecha_inicio',
        'rec_cli_cab_fecha_fin',
        'rec_cli_cab_estado',
        'rec_cli_cab_prioridad',
        'rec_cli_cab_observacion',
        'clientes_id',
        'empresa_id',
        'sucursal_id',
        'user_id',
        'venta_cab_id'
    ];
    protected $table = 'reclamo_cli_cab';
}
