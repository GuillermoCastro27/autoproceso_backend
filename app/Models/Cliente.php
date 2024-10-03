<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $fillable = [
        'cli_nombre',
        'cli_apellido',
        'cli_ruc',
        'cli_direccion',
        'cli_telefono',
        'cli_correo',
        'pais_id',
        'ciudad_id',
        'nacionalidad_id'
    ];
    protected $table = 'clientes';
}
