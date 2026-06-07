<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timbrado extends Model
{
    protected $table = 'timbrado';

    protected $fillable = [
        'tim_numero',
        'tim_establecimiento',
        'tim_punto_expedicion',
        'tim_fecha_inicio',
        'tim_fecha_fin',
        'tim_nro_desde',
        'tim_nro_hasta',
        'tim_nro_actual',
        'tim_estado',
        'tipo_comprobante_id',
        'empresa_id',
        'sucursal_id',
    ];

    protected $casts = [
        'tim_fecha_inicio' => 'date',
        'tim_fecha_fin'    => 'date',
    ];

    public function tipoComprobante()
    {
        return $this->belongsTo(TipoComprobante::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Devuelve el siguiente número de comprobante y lo reserva en la BD.
     * Marca el timbrado como agotado si se consumió el último número.
     */
    public function formatearComprobante(int $nro): string
    {
        return str_pad($this->tim_establecimiento ?? '001', 3, '0', STR_PAD_LEFT)
            . '-'
            . str_pad($this->tim_punto_expedicion ?? '001', 3, '0', STR_PAD_LEFT)
            . '-'
            . str_pad((string)$nro, 7, '0', STR_PAD_LEFT);
    }

    public function siguiente(): int
    {
        $this->increment('tim_nro_actual');
        $this->refresh();

        if ($this->tim_nro_actual >= $this->tim_nro_hasta) {
            $this->update(['tim_estado' => 'agotado']);
        }

        return $this->tim_nro_actual;
    }

    /** Timbrado vigente para una empresa/sucursal y tipo de comprobante dados. */
    public static function activo(int $empresaId, int $sucursalId, int $tipoComprobanteId): ?self
    {
        $hoy = now()->toDateString();

        return self::where('empresa_id',           $empresaId)
                   ->where('sucursal_id',          $sucursalId)
                   ->where('tipo_comprobante_id',  $tipoComprobanteId)
                   ->where('tim_estado',           'activo')
                   ->whereDate('tim_fecha_inicio', '<=', $hoy)
                   ->whereDate('tim_fecha_fin',    '>=', $hoy)
                   ->first();
    }
}
