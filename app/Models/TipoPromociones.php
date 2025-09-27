<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPromociones extends Model
{
    protected $table = 'tipo_promociones';
    protected $fillable =
    [
    'tipo_prom_descrip',
    'tipo_prom_nombre',
    'tipo_prom_fechaInicio',
    'tipo_prom_fechaFin'
    ]; 
    use HasFactory;
}
