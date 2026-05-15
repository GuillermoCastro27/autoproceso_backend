<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class ArqueoCaja extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;

    protected $table = 'arqueo_caja';

    /**
     * Campos asignables
     */
    protected $fillable = [
        'arqueo_fecha',
        'empresa_id',
        'sucursal_id',
        'apertura_cierre_caja_id',
        'funcionario_id',
        'tipo_arqueo',
        'estado'
    ];

    /**
     * Casts
     */
    protected $casts = [
        'arqueo_fecha'   => 'datetime',
    ];

    /* =====================================================
     * RELACIONES
     * ===================================================== */

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function aperturaCierreCaja()
    {
        return $this->belongsTo(AperturaCierreCaja::class, 'apertura_cierre_caja_id');
    }


}
