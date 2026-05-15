<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class NotaRemiVentDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['nota_remi_vent_id', 'item_id'];
    protected $table = 'nota_remi_vent_det';
    protected $fillable = [
    'nota_remi_vent_id',
    'item_id', 
    'nota_remi_vent_det_cantidad',
    'nota_remi_vent_det_precio'
    ];
    protected $primaryKey = null;
    public $incrementing = false; 
}
