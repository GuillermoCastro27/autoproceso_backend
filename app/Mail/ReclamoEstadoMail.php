<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReclamoEstadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $datos;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    public function build()
    {
        $asunto = match($this->datos['estado']) {
            'PENDIENTE'  => "Reclamo #{$this->datos['id']} recibido — AutoProcesos",
            'EN PROCESO' => "Reclamo #{$this->datos['id']} en proceso — AutoProcesos",
            'RESUELTO'   => "Reclamo #{$this->datos['id']} resuelto — AutoProcesos",
            'ANULADO'    => "Reclamo #{$this->datos['id']} anulado — AutoProcesos",
            default      => "Actualización de su reclamo #{$this->datos['id']} — AutoProcesos",
        };

        return $this->subject($asunto)
                    ->view('emails.reclamo_estado');
    }
}
