<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoVehiculoDet extends Model
{
    use HasFactory;

    protected $table = 'tipo_vehiculo_det';

    protected $fillable = [
        'tipo_vehiculo_id',
        'tv_det_placa',
        'tv_det_num_chasis',
        'tv_det_num_motor',
    ];
}
