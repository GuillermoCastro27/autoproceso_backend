<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PedidoVentasDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['pedidos_ventas_id', 'item_id'];

    protected $table = 'pedidos_ventas_det';

    protected $fillable = [
        'pedidos_ventas_id',
        'item_id',
        'det_cantidad',
        'cantidad_stock',
        'deposito_id',
        'marca_id',
        'modelo_id',
    ];

    public $incrementing = false; // clave compuesta
    protected $keyType = 'int';

    public function getKeyName()
    {
        return null;
    }

    public function getIncrementing()
    {
        return false;
    }
}
