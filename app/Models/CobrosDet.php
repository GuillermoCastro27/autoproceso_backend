<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class CobrosDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['cobros_cab_id', 'item_id'];
    protected $table = 'cobros_det';

    public $incrementing = false;

    protected $primaryKey = null;

    public $timestamps = true;
    protected $fillable = [
        'cobros_cab_id',
        'item_id',
        'cob_det_cantidad',
        'cob_det_precio',
        'tipo_impuesto_id'
    ];
}
