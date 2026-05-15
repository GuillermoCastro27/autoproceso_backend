<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class CtasPagar extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $table = 'ctas_pagar';

    // Define los campos que se pueden asignar masivamente
    protected $fillable = [
        'compra_cab_id',
        'nro_cuota',
        'cta_pag_monto',
        'cta_pag_fecha',
        'cta_pag_cuota',
        'cta_pag_estado',
        'condicion_pago'
    ];
}
