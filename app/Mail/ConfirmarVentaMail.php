<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmarVentaMail extends Mailable
{
    public $cliente;
    public $venta;

    public function __construct($cliente, $venta)
    {
        $this->cliente = $cliente;
        $this->venta = $venta;
    }

    public function build()
    {
        return $this->subject('Solicitud #' . $this->venta->serie . '-' . $this->venta->numero. ' confirmada')
            ->view('emails.confirmar_venta');
    }
}
