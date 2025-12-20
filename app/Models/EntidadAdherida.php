<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntidadAdherida extends Model
{
    protected $table = 'entidad_adherida';

    protected $fillable = [
        'entidad_emisora_id',
        'marca_tarjeta_id',
        'ent_adh_nombre',
        'ent_adh_direccion',
        'ent_adh_telefono',
        'ent_adh_email'
    ];
    public function entidadEmisora()
    {
        return $this->belongsTo(EntidadEmisora::class, 'entidad_emisora_id');
    }

    // 💳 Marca de Tarjeta
    public function marcaTarjeta()
    {
        return $this->belongsTo(MarcaTarjeta::class, 'marca_tarjeta_id');
    }
}
