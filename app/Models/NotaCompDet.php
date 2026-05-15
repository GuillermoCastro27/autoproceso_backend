<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class NotaCompDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['notas_comp_cab_id', 'item_id'];

    protected $table = 'notas_comp_det';

    protected $fillable = [
        'notas_comp_cab_id',
        'item_id',
        'deposito_id',
        'tipo_impuesto_id',
        'notas_comp_det_cantidad',
        'notas_comp_det_costo'
    ];
    public $incrementing = false;

    public function tipo_impuesto()
    {
        return $this->belongsTo(TipoImpuesto::class, 'tipo_impuesto_id');
    }
}

