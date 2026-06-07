<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Marca extends Model implements Auditable
{
    use HasFactory, AuditableTrait;
    protected $table = 'marca';
    protected $fillable = [
        'marc_nom',
        'mar_tipo',
        'marc_estado',
    ];
}
