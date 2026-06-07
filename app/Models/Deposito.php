<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Deposito extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $table = 'deposito'; // Nombre de la tabla en la BD

    protected $fillable = [
        'dep_nombre',
        'sucursal_id',
        'dep_estado',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'deposito_id', 'id');
    }
}
