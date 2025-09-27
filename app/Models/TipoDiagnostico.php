<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDiagnostico extends Model
{
    protected $table = 'tipo_diagnostico';
    protected $fillable =['tipo_diag_nombre','tipo_diag_descrip']; 
    use HasFactory;
}
