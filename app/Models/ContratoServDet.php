<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class ContratoServDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['contrato_serv_cab_id', 'item_id'];
    protected $fillable =[
        'contrato_serv_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'contrato_serv_det_cantidad',
        'contrato_serv_det_costo',
        'contrato_serv_det_cantidad_stock'
    ];
    protected $primaryKey = null;
    public $incrementing = false;
    protected $table = 'contrato_serv_det';
}
