<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;
    protected $fillable = [
        'fun_nom',
        'fun_apellido',
        'fun_direccion',
        'fun_telefono',
        'fun_correo',
        'fun_ci',
        'pais_id',
        'ciudad_id',
        'nacionalidad_id'
    ];
    protected $table = 'funcionario';
}
