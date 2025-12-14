<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipoTrabajo extends Model
{
    use HasFactory;
    protected $fillable = [
        'equipo_nombre',
        'equipo_descripcion',
        'equipo_categoria'
    ];
    protected $table = 'equipo_trabajo';
}
