<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class OrdenCompraDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['orden_compra_cab_id', 'item_id'];
    protected $fillable =['orden_compra_cab_id','item_id','deposito_id','tipo_impuesto_id','orden_compra_det_cantidad','orden_compra_det_costo','marca_id','modelo_id'];
    protected $primaryKey = null;
    public $incrementing = false;
    protected $table = 'orden_compra_det';
}
