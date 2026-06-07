<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoComprobante extends Model
{
    protected $table = 'tipo_comprobante';

    protected $fillable = ['tip_comp_nombre', 'tip_comp_abrev'];

    public function timbrados()
    {
        return $this->hasMany(Timbrado::class);
    }
}
