<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CobrosCheque extends Model
{
    use HasFactory;
    protected $table = 'cobros_cheque';

    protected $fillable = [
        'cobros_cab_id',
        'entidad_emisora_cheque_id',
        'nro_cheque',
        'fecha_vencimiento',
        'monto_cheque',
        'estado_cheque'
    ];
}
