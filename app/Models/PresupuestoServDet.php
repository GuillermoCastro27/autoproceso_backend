<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PresupuestoServDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['presupuesto_serv_cab_id', 'item_id'];
    protected $fillable =[
        'presupuesto_serv_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'pres_serv_det_cantidad',
        'pres_serv_det_costo',
        'pres_serv_det_cantidad_stock'
    ];
    protected $primaryKey = null;
    public $incrementing = false;
    protected $table = 'presupuesto_serv_det';
}
