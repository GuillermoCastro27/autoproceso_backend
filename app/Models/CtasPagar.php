<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CtasPagar extends Model
{
    use HasFactory;

    protected $table = 'ctas_pagar';

    // Define los campos que se pueden asignar masivamente
    protected $fillable = [
        'compra_cab_id',
        'cta_pag_monto',
        'cta_pag_fecha',
        'cta_pag_cuota',
        'cta_pag_estado',
        'condicion_pago'
    ];

    // Define la clave primaria correctamente
    protected $primaryKey = 'compra_cab_id';
}
