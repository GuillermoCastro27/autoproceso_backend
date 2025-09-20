<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;
    protected $fillable = [
        'prov_razonsocial',
        'prov_ruc',
        'prov_direccion',
        'prov_telefono',
        'prov_correo',
        'pais_id',
        'ciudad_id',
        'nacionalidad_id'
    ];
    protected $table = 'proveedores';
}
