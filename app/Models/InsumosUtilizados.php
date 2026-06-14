<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class InsumosUtilizados extends Model implements Auditable
{
    use AuditableTrait;

    protected $table = 'insumos_utilizados';

    protected $fillable = [
        'orden_serv_cab_id',
        'item_id',
        'tipo_impuesto_id',
        'ins_util_cantidad',
        'ins_util_costo',
        'ins_util_estado',
    ];
}
