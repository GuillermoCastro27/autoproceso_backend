<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class TipoImpuesto extends Model implements Auditable
{
    use HasFactory, AuditableTrait;
    protected $table = 'tipo_impuesto';
    protected $fillable = ['tip_imp_nom', 'tipo_imp_tasa'];
}
