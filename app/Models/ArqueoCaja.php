<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArqueoCaja extends Model
{
    use HasFactory;

    protected $table = 'arqueo_caja';

    /**
     * Campos asignables
     */
    protected $fillable = [
        'arqueo_fecha',
        'empresa_id',
        'sucursal_id',
        'apertura_cierre_caja_id',
        'user_id',
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

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
