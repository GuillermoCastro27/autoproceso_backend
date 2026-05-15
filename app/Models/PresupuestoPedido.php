<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestoPedido extends Model
{
    use HasFactory;

    protected $table    = 'presupuesto_pedidos';
    protected $fillable = ['presupuesto_id', 'pedido_id', 'pres_prov_ped_fecha_registro'];

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
