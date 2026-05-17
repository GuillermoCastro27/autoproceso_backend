<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UsuarioBloqueadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUsuario;
    public string $ip;
    public string $fechaHora;

    public function __construct(string $loginUsuario, string $ip)
    {
        $this->loginUsuario = $loginUsuario;
        $this->ip           = $ip;
        $this->fechaHora    = now()->format('d/m/Y H:i:s');
    }

    public function build()
    {
        return $this->subject('⚠️ Usuario bloqueado por intentos fallidos — AutoProcesos')
            ->view('emails.usuario_bloqueado');
    }
}
