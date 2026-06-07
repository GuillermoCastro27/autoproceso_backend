<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Sucursal extends Model implements Auditable
{
    use HasFactory, AuditableTrait;
    protected $fillable = [
        'empresa_id',
        'suc_razon_social',
        'suc_direccion',
        'suc_telefono',
        'suc_correo',
        'suc_estado',
    ];

    protected $table = 'sucursal';

    protected $primaryKey = 'id';
    public $incrementing = true;
}