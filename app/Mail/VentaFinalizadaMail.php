<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VentaFinalizadaMail extends Mailable
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
        return $this->subject('Pedido #' . $this->venta->serie . '-' . $this->venta->numero . ' finalizado')
            ->view('emails.venta_finalizada');
    }
}