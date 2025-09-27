<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDescuentos extends Model
{
    protected $table = 'tipo_descuentos';
    protected $fillable =
    [
    'tipo_desc_descrip',
    'tipo_desc_nombre',
    'tipo_desc_fechaInicio',
    'tipo_desc_fechaFin'
    ]; 
    use HasFactory;
}
