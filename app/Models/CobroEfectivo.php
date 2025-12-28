<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CobroEfectivo extends Model
{
    protected $table = 'cobro_efectivo';

    protected $fillable = [
        'cobros_cab_id',
        'monto_efectivo'
    ];
}

