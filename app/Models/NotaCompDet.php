<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaCompDet extends Model
{
    use HasFactory;

    protected $table = 'notas_comp_det';

    protected $fillable = [
        'notas_comp_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'notas_comp_det_cantidad',
        'notas_comp_det_costo'
    ];

    protected $primaryKey = ['notas_comp_cab_id', 'item_id'];
    public $incrementing = false;

    public function tipo_impuesto()
    {
        return $this->belongsTo(TipoImpuesto::class, 'tipo_impuesto_id');
    }
}

