<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CtasCobrar extends Model
{
    use HasFactory;

    protected $table = 'ctas_cobrar';

    protected $fillable = [
        'ventas_cab_id',
        'nro_cuota',
        'cta_cob_monto',
        'cta_cob_fecha_vencimiento',
        'cta_cob_estado',
        'condicion_pago'
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
}
