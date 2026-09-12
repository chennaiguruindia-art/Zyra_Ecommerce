<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmed – ' . $this->order->order_number . ' ✨',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.order-confirmation',
            with: [
                'order' => $this->order,
                'items' => $this->order->items ?? [],
                'customerName' => $this->order->customer_name,
                'orderNumber' => $this->order->order_number,
                'paymentMethod' => $this->order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online (Razorpay)',
                'subtotal' => (float) $this->order->subtotal,
                'discount' => (float) $this->order->discount,
                'tax' => (float) $this->order->tax,
                'total' => (float) $this->order->total,
                'shippingAddress' => $this->order->shipping_address . ', ' . $this->order->city . ', ' . $this->order->state . ' – ' . $this->order->pincode,
                'trackUrl' => url('/my-orders'),
            ],
        );
    }
}