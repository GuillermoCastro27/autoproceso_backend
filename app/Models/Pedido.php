<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Pedido extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;

    protected $fillable = [
        'ped_fecha',
        'ped_vence',
        'ped_pbservaciones',
        'ped_estado',
        'funcionario_id',
        'empresa_id',
        'sucursal_id'
    ];

    protected $casts = [
        'ped_fecha' => 'datetime:Y-m-d H:i:s',
        'ped_vence' => 'datetime:Y-m-d H:i:s',
    ];

    public function setPedFechaAttribute($value)
    {
        $this->attributes['ped_fecha'] = $this->convertirFecha($value);
    }

    public function setPedVenceAttribute($value)
    {
        $this->attributes['ped_vence'] = $this->convertirFecha($value);
    }

    private function convertirFecha($value)
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y H:i:s', $value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return $value;
        }
    }
}