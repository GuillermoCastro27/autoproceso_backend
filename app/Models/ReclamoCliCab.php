<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class ReclamoCliCab extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
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
        'funcionario_id',
        'venta_cab_id'
    ];
    protected $table = 'reclamo_cli_cab';
}
