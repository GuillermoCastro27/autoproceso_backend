<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Item extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;
    protected $fillable = [
        'item_decripcion',
        'item_costo',
        'item_precio',
        'tipo_id',
        'tipo_impuesto_id',
    ];
}
