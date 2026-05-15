<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class VentasCab extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $table = 'ventas_cab';

    // Define los campos que se pueden asignar masivamente
    protected $fillable = [
        'vent_intervalo_fecha_vence',
        'vent_fecha',
        'vent_estado',
        'vent_cant_cuota',
        'condicion_pago',
        'funcionario_id',
        'pedidos_ventas_id',
        'clientes_id',
        'empresa_id',
        'sucursal_id'
    ];
    public function cliente()
{
    return $this->belongsTo(Clientes::class, 'clientes_id');
}

public function pedidoVenta()
{
    return $this->belongsTo(PedidoVentas::class, 'pedidos_ventas_id');
}
}
