<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class AjusteCab extends Model implements Auditable
{
    protected $table = 'ajuste_cab';
    use HasFactory;
    use AuditableTrait;
    protected $fillable = [
        'ajus_cab_fecha',
        'ajus_cab_estado', 
        'tipo_ajuste',
        'funcionario_id',
        'empresa_id', 
        'sucursal_id', 
        'motivo_ajuste_id'
        ];
}
