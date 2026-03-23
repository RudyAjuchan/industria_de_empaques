<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutClienteMail extends Mailable
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
        return $this->subject('Hemos recibido tu solicitud #' . $this->venta->serie . '-' . $this->venta->numero)
            ->view('emails.checkout_cliente');
    }
}