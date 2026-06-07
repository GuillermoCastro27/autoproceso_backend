<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class OrdenServDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['orden_serv_cab_id', 'item_id'];
    protected $fillable =[
        'orden_serv_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'orden_serv_det_cantidad',
        'orden_serv_det_costo',
        'orden_serv_det_cantidad_stock',
        'marca_id',
        'modelo_id',
    ];
    protected $primaryKey = null;
    public $incrementing = false;
    protected $table = 'orden_serv_det';
}
