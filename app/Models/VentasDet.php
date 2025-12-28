<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentasDet extends Model
{
    use HasFactory;

    protected $table = 'ventas_det';

    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'ventas_cab_id',
        'item_id',
        'vent_det_cantidad',
        'vent_det_precio',
        'tipo_impuesto_id'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function venta()
    {
        return $this->belongsTo(VentasCab::class, 'ventas_cab_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function tipoImpuesto()
    {
        return $this->belongsTo(TipoImpuesto::class, 'tipo_impuesto_id');
    }
}
