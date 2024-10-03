<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use HasFactory;
    protected $fillable =['pais_descrpcion','pais_gentilicio','pais_siglas'];

    protected $table = 'paises';
}
