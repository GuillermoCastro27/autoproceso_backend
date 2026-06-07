<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Tipo extends Model implements Auditable
{
    use HasFactory, AuditableTrait;
    protected $fillable = ['tipo_descripcion', 'tipo_objeto', 'tipo_estado'];
}
