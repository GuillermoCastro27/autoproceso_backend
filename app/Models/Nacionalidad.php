<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nacionalidad extends Model
{
    protected $table = 'nacionalidad';
    protected $fillable = ['nacio_descripcion', 'pais_id'];
    use HasFactory;

    public function pais()
    {
        return $this->belongsTo(\App\Models\Pais::class, 'pais_id');
    }
}
