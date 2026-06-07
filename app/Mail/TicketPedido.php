<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketPedido extends Mailable
{
    use Queueable, SerializesModels;

    public array $datos;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    public function build()
    {
        $nro = str_pad($this->datos['id'], 7, '0', STR_PAD_LEFT);

        return $this->subject("Pedido de Compra N° {$nro} — AutoProcesos")
                    ->view('emails.ticket_pedido');
    }
}
