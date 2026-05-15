<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accesos extends Model
{
    use HasFactory;
    protected $fillable = ['permiso_id', 'perfil_id', 'mod_id', 'acc_estado', 'acc_fecha'];
    protected $table = 'accesos';

    public function permiso()
    {
        return $this->belongsTo(Permiso::class);
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'mod_id');
    }
}
