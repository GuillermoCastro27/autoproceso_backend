<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaCobro extends Model
{
    protected $table = 'forma_cobro';

    protected $fillable = [
        'for_cob_descripcion'
    ];
}
