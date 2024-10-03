<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoImpuesto extends Model
{
    protected $table = 'tipo_impuesto';
    protected $fillable =['tip_imp_nom','tipo_imp_tasa'];
    use HasFactory;
}
