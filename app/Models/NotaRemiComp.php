<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class NotaRemiComp extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $table = 'nota_remi_comp';
    protected $fillable =[
        'nota_remi_fecha',
        'nota_remi_observaciones',
        'nota_remi_estado',
        'funcionario_id',
        'empresa_id',
        'sucursal_id'
        ];
}
