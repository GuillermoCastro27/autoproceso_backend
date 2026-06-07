<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Cliente extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;
    protected $fillable = [
        'cli_nombre',
        'cli_apellido',
        'cli_ruc',
        'cli_direccion',
        'cli_telefono',
        'cli_correo',
        'pais_id',
        'ciudad_id',
        'nacionalidad_id',
        'cli_estado',
        'cli_tipo_persona',
        'cli_razon_social',
    ];
    protected $table = 'clientes';
}
