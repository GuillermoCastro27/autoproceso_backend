<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;
    protected $fillable = [
        'emp_razon_social',
        'emp_direccion',
        'emp_telefono',
        'emp_correo'
    ];
    protected $table = 'empresa';
}
