<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemModelo extends Model
{
    use HasFactory;
    protected $table = 'item_modelo';
    protected $fillable =['modelo_id','item_id','item_modelo_descrip'];
    protected $primaryKey =['modelo_id','item_id'];
    public $incrementing = false;
}
