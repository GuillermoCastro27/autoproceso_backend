<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class SolicitudDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['solicitudes_cab_id', 'item_id'];
    protected $fillable =[
        'solicitudes_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'soli_det_cantidad',
        'soli_det_costo',
        'soli_det_cantidad_stock'
    ];
    protected $primaryKey = null;
    public $incrementing = false;
    protected $table = 'solicitudes_det';
}
