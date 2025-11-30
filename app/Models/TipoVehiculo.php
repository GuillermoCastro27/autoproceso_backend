<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoVehiculo extends Model
{
    use HasFactory;

    protected $table = 'tipo_vehiculo';

    protected $fillable = [
        'tip_veh_nombre',
        'tip_veh_capacidad',
        'tip_veh_combustible',
        'tip_veh_categoria',
        'tip_veh_observacion',
        'marca_id',
        'modelo_id'
    ];
}