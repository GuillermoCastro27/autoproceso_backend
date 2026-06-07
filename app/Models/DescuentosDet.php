<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class DescuentosDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['descuentos_cab_id', 'item_id'];
    protected $fillable = [
        'descuentos_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'desc_det_cantidad',
        'desc_det_costo',
        'marca_id',
        'modelo_id',
    ];
    protected $primaryKey = null;
    public $incrementing = false;
    protected $table = 'descuentos_det';
}
