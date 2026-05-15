<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class CompraDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['compra_cab_id', 'item_id'];
    protected $fillable =['compra_cab_id','item_id','tipo_impuesto_id','comp_det_cantidad','comp_det_costo','deposito_id'];
    protected $primaryKey = null;
    public $incrementing = false;
    protected $table = 'compra_det';
}
