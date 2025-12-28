<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CobrosDet extends Model
{
    use HasFactory;
    protected $table = 'cobros_det';

    public $incrementing = false;

    protected $primaryKey = ['cobros_cab_id', 'item_id'];

    public $timestamps = true;
    protected $fillable = [
        'cobros_cab_id',
        'item_id',
        'cob_det_cantidad',
        'cob_det_precio',
        'tipo_impuesto_id'
    ];
}
