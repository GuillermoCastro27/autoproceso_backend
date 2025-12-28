<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentasCab extends Model
{
    use HasFactory;
    protected $table = 'ventas_cab';

    // Define los campos que se pueden asignar masivamente
    protected $fillable = [
        'vent_intervalo_fecha_vence',
        'vent_fecha',
        'vent_estado',
        'vent_cant_cuota',
        'condicion_pago',
        'user_id',
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
