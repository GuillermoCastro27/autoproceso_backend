<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotasVentDet extends Model
{
    use HasFactory;

    protected $table = 'notas_vent_det';

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'notas_vent_cab_id',
        'item_id',
        'notas_vent_det_cantidad',
        'notas_vent_det_precio',
        'tipo_impuesto_id'
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
