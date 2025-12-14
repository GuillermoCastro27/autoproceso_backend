<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Cliente;
use App\Models\TipoServicio;
use App\Models\User;

class ContratoServCab extends Model
{
    use HasFactory;

    protected $table = 'contrato_serv_cab';

    protected $fillable = [
        'contrato_fecha',
        'contrato_fecha_inicio',
        'contrato_fecha_fin',
        'contrato_intervalo_fecha_vence',
        'contrato_estado',
        'contrato_condicion_pago',
        'contrato_cuotas',
        'contrato_tipo',
        'contrato_objeto',
        'contrato_alcance',
        'contrato_responsabilidad',
        'contrato_garantia',
        'contrato_limitacion',
        'contrato_fuerza_mayor',
        'contrato_jurisdiccion',
        'contrato_observacion',
        'contrato_archivo_url',
        'empresa_id',
        'sucursal_id',
        'clientes_id',
        'tipo_servicio_id',
        'user_id'
    ];
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function sucursal()
    {
        // OJO: tu FK de sucursal es rara (referencia empresa_id en sucursal)
        // Si tu sucursal realmente usa empresa_id como PK, esto funciona así:
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'empresa_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'clientes_id');
    }

    public function tipoServicio()
    {
        return $this->belongsTo(TipoServicio::class, 'tipo_servicio_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
