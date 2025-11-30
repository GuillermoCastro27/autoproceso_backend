<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoServicio extends Model
{
    protected $table = 'tipo_servicio';
    protected $fillable =['tipo_serv_nombre','tip_serv_precio']; 
    use HasFactory;
}
