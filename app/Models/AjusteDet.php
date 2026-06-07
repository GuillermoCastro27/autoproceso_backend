<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class AjusteDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['ajuste_cab_id', 'item_id', 'deposito_id'];
    protected $table = 'ajuste_det';
    protected $fillable = [
    'ajuste_cab_id',
    'item_id',
    'deposito_id',
    'cantidad_stock',
    'ajus_det_cantidad'
    ];
    protected $primaryKey = null;
    public $incrementing = false; 
}
