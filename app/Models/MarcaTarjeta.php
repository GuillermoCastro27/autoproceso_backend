<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarcaTarjeta extends Model
{
    use HasFactory;
    protected $table = 'marca_tarjeta';

    protected $fillable = [
        'marca_nombre'
    ];
}
