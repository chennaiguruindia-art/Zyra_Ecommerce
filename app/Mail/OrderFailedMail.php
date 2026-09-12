<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderFailedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $pending)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your ZYRA payment needs attention 🙏',
        );
    }

    public function content(): Content
    {
        $pending = $this->pending;

        return new Content(
            view: 'mail.order-failed',
            with: [
                'customerName' => trim(($pending['_customer_name'] ?? '') ?: (($pending['first_name'] ?? '') . ' ' . ($pending['last_name'] ?? ''))),
                'email' => $pending['email'] ?? '',
                'orderId' => $pending['_rzp_order_id'] ?? '',
                'total' => (float) ($pending['_total'] ?? 0),
                'shoppingUrl' => url('/cart'),
                'shopUrl' => url('/shop'),
            ],
        );
    }
}