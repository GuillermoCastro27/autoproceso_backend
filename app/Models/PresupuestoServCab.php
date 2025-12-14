<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestoServCab extends Model
{
    use HasFactory;
    protected $fillable = [
        'pres_serv_cab_observaciones',
        'pres_serv_cab_fecha',
        'pres_serv_cab_fecha_vence',
        'pres_serv_cab_estado',
        'user_id',
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
