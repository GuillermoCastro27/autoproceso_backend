<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Funcionario extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;
    protected $fillable = [
        'fun_nom',
        'fun_apellido',
        'fun_direccion',
        'fun_telefono',
        'fun_correo',
        'fun_ci',
        'pais_id',
        'ciudad_id',
        'nacionalidad_id'
    ];
    protected $table = 'funcionario';
}
