<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;
    protected $fillable = [
        'mod_nombre',
        'mod_descripcion',
        'mod_estado'
    ];
    protected $table = 'modulos';

    public function accesos()
    {
        return $this->hasMany(Accesos::class, 'mod_id');
    }
}
