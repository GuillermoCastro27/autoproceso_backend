<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposito extends Model
{
    use HasFactory;

    protected $table = 'deposito'; // Nombre de la tabla en la BD

    protected $fillable = [
        'item_id',
        'cantidad'
    ];

    /**
     * Relación con la tabla Item (un depósito pertenece a un item).
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
