<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class OrdenCompraCab extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'ord_comp_intervalo_fecha_vence',
        'ord_comp_fecha',
        'ord_comp_estado',
        'ord_comp_cant_cuota',
        'funcionario_id',
        'presupuesto_id',
        'pedido_id',
        'proveedor_id',
        'empresa_id',
        'condicion_pago',
        'sucursal_id'
    ];
    protected $table = 'orden_compra_cab';
}
