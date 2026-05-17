<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class TipoServicio extends Model implements Auditable
{
    use HasFactory, AuditableTrait;
    protected $table = 'tipo_servicio';
    protected $fillable = ['tipo_serv_nombre', 'tip_serv_precio'];
}
