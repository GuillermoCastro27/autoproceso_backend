<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibroVentas extends Model
{
    use HasFactory;

    protected $table = 'libro_ventas';

    protected $primaryKey = 'ventas_cab_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'ventas_cab_id',
        'libV_monto',
        'libV_fecha',
        'condicion_pago',
        'libV_cuota',
        'clientes_id',
        'cli_nombre',
        'cli_apellido',
        'cli_ruc',
        'tipo_impuesto_id',
        'tip_imp_nom'
    ];
}
