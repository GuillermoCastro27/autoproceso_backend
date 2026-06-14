<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class RecaudacionDepositar extends Model implements Auditable
{
    use AuditableTrait;

    protected $table = 'recaudaciones_depositar';

    protected $fillable = [
        'apertura_cierre_caja_id',
        'reca_dep_met_pago',
        'reca_dep_estado',
        'reca_dep_fecha',
        'reca_dep_obs',
    ];

    protected $casts = [
        'reca_dep_fecha' => 'datetime',
    ];

    public function aperturaCierreCaja()
    {
        return $this->belongsTo(AperturaCierreCaja::class, 'apertura_cierre_caja_id');
    }
}
