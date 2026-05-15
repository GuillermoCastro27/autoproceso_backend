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

    protected $primaryKey = 'id';
    public $incrementing = true;
}