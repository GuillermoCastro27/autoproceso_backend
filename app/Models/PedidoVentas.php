<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PedidoVentas extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable =[
    'ped_ven_fecha',
    'ped_ven_vence',
    'ped_ven_observaciones',
    'ped_ven_estado',
    'funcionario_id',
    'empresa_id',
    'sucursal_id',
    'clientes_id'
    ];
    protected $table = 'pedidos_ventas';
}
