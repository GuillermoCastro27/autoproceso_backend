<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Empresa extends Model implements Auditable
{
    use HasFactory, AuditableTrait;
    protected $fillable = [
        'emp_razon_social',
        'emp_direccion',
        'emp_telefono',
        'emp_correo'
    ];
    protected $table = 'empresa';
}
