<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginIntento extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'login',
        'resultado',
        'ip',
        'user_agent',
    ];
}
