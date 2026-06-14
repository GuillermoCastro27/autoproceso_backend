<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class NotaRemiComp extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    protected $table = 'nota_remi_comp';
    protected $fillable = [
        'nota_remi_fecha',
        'nota_remi_observaciones',
        'nota_remi_estado',
        'tipo',
        'timbrado_id',
        'nota_remi_nro_comp',
        'proveedor_id',
        'nota_remi_nro',
        'nota_remi_fecha_emision',
        'sucursal_destino_id',
        'chofer_nombre',
        'chofer_documento',
        'chofer_telefono',
        'vehiculo_matricula',
        'vehiculo_modelo',
        'vehiculo_color',
        'vehiculo_anio',
        'vehiculo_nro',
        'tipo_vehiculo',
        'funcionario_id',
        'conductor_id',
        'tipo_vehiculo_det_id',
        'empresa_id',
        'sucursal_id',
    ];
}
