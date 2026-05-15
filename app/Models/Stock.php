<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stock';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'deposito_id',
        'item_id',
        'cantidad',
        'cantidad_minima',
        'cantidad_maxima',
    ];

    public function deposito()
    {
        return $this->belongsTo(Deposito::class, 'deposito_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
