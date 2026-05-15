<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class DescuentosCab extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $table = 'descuentos_cab';

    protected $fillable = [
        'desc_cab_nombre',
        'desc_cab_observaciones',
        'desc_cab_fecha_registro',
        'desc_cab_fecha_inicio',
        'desc_cab_fecha_fin',
        'desc_cab_estado',
        'desc_cab_porcentaje',
        'desc_cab_monto',
        'funcionario_id',
        'empresa_id',
        'sucursal_id',
        'tipo_descuentos_id',
    ];
}
