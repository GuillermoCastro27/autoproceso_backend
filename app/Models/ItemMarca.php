<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemMarca extends Model
{
    use HasFactory;
    protected $table = 'item_marca';
    protected $fillable =['marca_id','item_id','item_marca_descrip'];
    public $incrementing = false;
}
