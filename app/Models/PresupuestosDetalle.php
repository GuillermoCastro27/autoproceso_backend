<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PresupuestosDetalle extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $fillable = ['presupuesto_id', 'item_id', 'deposito_id', 'det_costo', 'det_cantidad'];
    protected $primaryKey = 'id';
    public $incrementing = true;
}
