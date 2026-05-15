<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class DiagnosticoDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['diagnostico_cab_id', 'item_id'];
    protected $fillable =[
        'diagnostico_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'diag_det_cantidad',
        'diag_det_costo',
        'diag_det_cantidad_stock'
    ];
    protected $primaryKey = null;
    public $incrementing = false;
    protected $table = 'diagnostico_det';
}
