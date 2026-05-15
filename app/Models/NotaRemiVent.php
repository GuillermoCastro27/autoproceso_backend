<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class NotaRemiVent extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $table = 'nota_remi_vent';
    protected $fillable =[
        'nota_remi_vent_fecha',
        'nota_remi_vent_observaciones',
        'nota_remi_vent_estado',
        'funcionario_id',
        'empresa_id',
        'sucursal_id',
        'ventas_cab_id',
        'clientes_id'
    ];
}
