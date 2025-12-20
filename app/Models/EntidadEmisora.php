<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntidadEmisora extends Model
{
    use HasFactory;
    protected $fillable = [
        'ent_emis_nombre',
        'ent_emis_direccion',
        'ent_emis_telefono',
        'ent_emis_email',
        'ent_emis_estado'
    ];
    protected $table = 'entidad_emisora';
}
