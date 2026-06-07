<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class NotaCompCab extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'nota_comp_intervalo_fecha_vence',
        'nota_comp_fecha',
        'nota_comp_estado',
        'nota_comp_cant_cuota',
        'nota_comp_tipo',
        'nota_comp_afecta_stock',
        'nota_comp_observaciones',
        'nota_comp_timbrado',
        'nota_comp_nro_nota',
        'nota_comp_condicion_pago',
        'funcionario_id',
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
