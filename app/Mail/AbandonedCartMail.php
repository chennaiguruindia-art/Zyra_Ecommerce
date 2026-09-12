<?php

namespace App\Mail;

use App\Models\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedCartMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Cart $cart)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your ZYRA cart is waiting for you ❤️',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.abandoned-cart',
            with: [
                'cart' => $this->cart,
                'items' => $this->cart->items ?? [],
                'subtotal' => (float) ($this->cart->subtotal ?? 0),
                'checkoutUrl' => url('/cart'),
            ],
        );
    }
}