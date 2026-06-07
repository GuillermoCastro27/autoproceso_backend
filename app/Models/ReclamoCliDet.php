<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class ReclamoCliDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['reclamo_cli_cab_id', 'item_id'];
    protected $fillable = [
        'reclamo_cli_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'rec_cli_det_cantidad',
        'rec_cli_det_costo',
        'rec_cli_det_cantidad_stock',
        'marca_id',
        'modelo_id',
    ];
    protected $primaryKey = null;
    public $incrementing = false;
    protected $table = 'reclamo_cli_det';
}
