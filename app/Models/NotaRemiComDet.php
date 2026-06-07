<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class NotaRemiComDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['nota_remi_comp_id', 'item_id'];
    protected $table = 'nota_remi_com_det';
    protected $fillable = [
        'nota_remi_comp_id',
        'item_id',
        'deposito_id',
        'deposito_destino_id',
        'nota_remi_com_det_cantidad',
    ];
    protected $primaryKey = null;
    public $incrementing = false; 
}
