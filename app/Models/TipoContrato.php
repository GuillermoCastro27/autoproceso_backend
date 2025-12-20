<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoContrato extends Model
{
    use HasFactory;
    protected $table = 'tipo_contrato';
    protected $fillable =
    [
    'tip_con_nombre',
    'tip_con_objeto',
    'tip_con_alcance',
    'tip_con_garantia',
    'tip_con_responsabilidad',
    'tip_con_limitacion',
    'tip_con_fuerza_mayor',
    'tip_con_jurisdiccion',
    'tip_con_estado'
    ];
}
