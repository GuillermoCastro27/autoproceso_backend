<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompositeKeyAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class NotasVentDet extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait, CompositeKeyAuditable {
        CompositeKeyAuditable::transformAudit insteadof AuditableTrait;
    }
    protected array $auditKeyColumns = ['notas_vent_cab_id', 'item_id'];

    protected $table = 'notas_vent_det';

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'notas_vent_cab_id',
        'item_id',
        'notas_vent_det_cantidad',
        'notas_vent_det_precio',
        'tipo_impuesto_id',
        'deposito_id',
        'marca_id',
        'modelo_id',
    ];

    /**
     * 🔹 RELACIÓN CORRECTA
     */
    public function tipoImpuesto()
    {
        return $this->belongsTo(
            TipoImpuesto::class,
            'tipo_impuesto_id',
            'id'
        );
    }
}
