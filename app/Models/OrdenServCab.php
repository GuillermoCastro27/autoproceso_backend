<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenServCab extends Model
{
    use HasFactory;
     protected $fillable = [
        'ord_serv_fecha',
        'ord_serv_fecha_vence',
        'ord_serv_estado',
        'ord_serv_tipo',
        'ord_serv_observaciones',
        'user_id',
        'presupuesto_serv_cab_id',
        'clientes_id',
        'equipo_trabajo_id',
        'diagnostico_cab_id',
        'tipo_diagnostico_id',
        'tipo_vehiculo_id',
        'empresa_id',
        'sucursal_id',
    ];

    protected $table = 'orden_serv_cab';
}
