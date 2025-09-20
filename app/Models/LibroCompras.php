<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibroCompras extends Model
{
    use HasFactory;

    protected $table = 'libro_compras';

    // Define los campos que se pueden asignar masivamente
    protected $fillable = [
        'compra_cab_id',
        'libC_monto',
        'libC_fecha',
        'libC_cuota',
        'libC_tipo_nota',
        'libC_estado',
        'proveedor_id',
        'prov_razonsocial',  // Agregado
        'prov_ruc',           // Agregado
        'tipo_impuesto_id',
        'tip_imp_nom',        // Agregado
        'condicion_pago'
    ];

    // Define la clave primaria correctamente
    protected $primaryKey = 'compra_cab_id';

    public $incrementing = false; // Laravel no tratará la clave primaria como autoincrementable

    // Relación con proveedores
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    // Relación con tipo de impuesto
    public function tipoImpuesto()
    {
        return $this->belongsTo(TipoImpuesto::class, 'tipo_impuesto_id');
    }
}
