<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AperturaCierreCaja extends Model
{
    use HasFactory;

    protected $table = 'apertura_cierre_caja';

    protected $fillable = [
        // 🔗 Apertura
        'empresa_id',
        'sucursal_id',
        'caja_id',
        'user_id',
        'fecha_apertura',
        'monto_apertura',

        // 🔒 Estado
        'estado',

        // 🕒 Cierre
        'fecha_cierre',

        // 💰 Totales cierre
        'monto_efectivo_cierre',
        'monto_tarjeta_cierre',
        'monto_cheque_cierre',
    ];

    // =========================
    // 🔹 Relaciones (opcional)
    // =========================

    public function usuarioApertura()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuarioCierre()
    {
        return $this->belongsTo(User::class, 'user_cierre_id');
    }
}
