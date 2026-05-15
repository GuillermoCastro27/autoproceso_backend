<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PedidosDetalle extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['pedidos_id', 'item_id'];

    protected $fillable = ['pedidos_id', 'item_id', 'deposito_id', 'det_cantidad', 'cantidad_stock'];

    public $incrementing = false; // No es autoincremental porque es clave compuesta
    protected $keyType = 'int'; // Asegura que Laravel trate los IDs como enteros

    public function getKeyName()
    {
        return null; // Laravel no debe asumir una clave primaria única
    }

    public function getIncrementing()
    {
        return false;
    }
}

