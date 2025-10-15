<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosticoDet extends Model
{
    use HasFactory;
    protected $fillable =[
        'diagnostico_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'diag_det_cantidad',
        'diag_det_costo',
        'diag_det_cantidad_stock'
    ];
    protected $primaryKey =[
        'diagnostico_cab_id',
        'item_id'];
    public $incrementing = false;
    protected $table = 'diagnostico_det';
}
