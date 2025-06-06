<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaCompCab extends Model
{
    use HasFactory;
    protected $fillable = [
        'nota_comp_intervalo_fecha_vence',
        'nota_comp_fecha',
        'nota_comp_estado',
        'nota_comp_cant_cuota',
        'nota_comp_tipo',
        'nota_comp_observaciones',
        'nota_comp_condicion_pago',
        'user_id',
        'compra_cab_id',
        'empresa_id',
        'sucursal_id'
    ];
    protected $table = 'notas_comp_cab';

    public function compra()
{
    return $this->belongsTo(CompraCab::class, 'compra_cab_id');
}
}
