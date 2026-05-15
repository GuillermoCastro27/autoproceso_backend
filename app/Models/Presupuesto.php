<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Presupuesto extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'pre_observaciones',
        'pre_estado',
        'pre_fecha',
        'pre_vence',
        'proveedor_id',
        'pedido_id',
        'funcionario_id',
        'empresa_id',
        'sucursal_id'
    ];

    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'presupuesto_pedidos')
                    ->withPivot('pres_prov_ped_fecha_registro')
                    ->withTimestamps();
    }

    public function presupuestoPedidos()
    {
        return $this->hasMany(PresupuestoPedido::class);
    }
}
