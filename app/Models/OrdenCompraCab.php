<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenCompraCab extends Model
{
    use HasFactory;
    protected $fillable = [
        'ord_comp_intervalo_fecha_vence',
        'ord_comp_fecha',
        'ord_comp_estado',
        'ord_comp_cant_cuota',
        'user_id',
        'presupuesto_id',
        'proveedor_id',
        'empresa_id',
        'sucursal_id'
    ];
    protected $table = 'orden_compra_cab';
}
