<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class CompraCab extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $table = 'compra_cab';

    // Define los campos que se pueden asignar masivamente
    protected $fillable = [
        'comp_intervalo_fecha_vence',
        'comp_fecha',
        'comp_estado',
        'comp_cant_cuota',
        'condicion_pago',
        'comp_timbrado',
        'funcionario_id',
        'orden_compra_cab_id',
        'proveedor_id',
        'empresa_id',
        'sucursal_id',
        'deposito_id',
    ];
    public function proveedor()
{
    return $this->belongsTo(Proveedor::class, 'proveedor_id');
}
}
