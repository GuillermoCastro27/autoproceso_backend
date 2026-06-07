<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class RecepcionDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['recep_cab_id', 'item_id'];
    protected $fillable =[
        'recep_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'recep_det_cantidad',
        'recep_det_costo',
        'recep_det_cantidad_stock',
        'marca_id',
        'modelo_id',
    ];
    protected $primaryKey = null;
    public $incrementing = false;
    protected $table = 'recep_det';
}
