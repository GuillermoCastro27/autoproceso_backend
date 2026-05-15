<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'login',
        'intentos',
        'bloqueado_hasta',
        'funcionario_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_code',
        'two_factor_expires_at',
        'intentos',
    ];
    public function perfil(){
        return $this->belongsTo(Perfil::class);
    }

    public function funcionario(){
        return $this->belongsTo(Funcionario::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $url = 'http://localhost/taller_front/index.html?token=' . $token . '&email=' . urlencode($this->email);
        Mail::to($this->email)->send(new ResetPasswordMail($url));
    }
}
