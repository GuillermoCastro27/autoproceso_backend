<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;
    protected $fillable = [
        'empresa_id',
        'suc_razon_social',
        'suc_direccion',
        'suc_telefono',
        'suc_correo'
    ];

    protected $table = 'sucursal';

    // Declarar empresa_id como clave primaria
    protected $primaryKey = 'empresa_id';

    // Si la clave primaria no es un auto-incremental
    public $incrementing = false;
}