<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PresupuestoServCab extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'pres_serv_cab_observaciones',
        'pres_serv_cab_fecha',
        'pres_serv_cab_fecha_vence',
        'pres_serv_cab_estado',
        'funcionario_id',
        'empresa_id',
        'sucursal_id',
        'diagnostico_cab_id',
        'tipo_servicio_id',
        'tipo_vehiculo_id',
        'promociones_cab_id',
        'descuentos_cab_id',
        'clientes_id'
    ];
    protected $table = 'presupuesto_serv_cab';
}
