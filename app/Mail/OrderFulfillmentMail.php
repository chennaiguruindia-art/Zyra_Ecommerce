<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderFulfillmentMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Order $order,
        public string $event = 'tracking_updated', // 'tracking_updated' or 'status_updated'
        public ?string $status = null,
        public ?string $awbCode = null,
        public ?string $courierName = null
    ) {
        $this->status = $status ?? $order->order_status;
        $this->awbCode = $awbCode ?? $order->awb_code;
        $this->courierName = $courierName ?? $order->courier_name;
    }

    public function envelope(): Envelope
    {
        $statusLower = strtolower($this->status ?? '');
        $subject = match ($statusLower) {
            'shipped' => 'Your ZYRA Order has been Shipped! 🚚 – ' . $this->order->order_number,
            'delivered' => 'Your ZYRA Order has been Delivered! 🎁 – ' . $this->order->order_number,
            'processing' => 'Your ZYRA Order is being processed – ' . $this->order->order_number,
            'cancelled' => 'Update regarding your ZYRA Order – ' . $this->order->order_number,
            default => ($this->awbCode ? 'Tracking Added for your ZYRA Order – ' : 'Status Update on your ZYRA Order – ') . $this->order->order_number,
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $trackingUrl = $this->resolveTrackingUrl();

        return new Content(
            view: 'mail.order-fulfillment',
            with: [
                'order' => $this->order,
                'items' => $this->order->items ?? [],
                'customerName' => $this->order->customer_name,
                'orderNumber' => $this->order->order_number,
                'status' => $this->status,
                'awbCode' => $this->awbCode,
                'courierName' => $this->courierName ?: 'Express Courier',
                'trackingUrl' => $trackingUrl,
                'shippingAddress' => $this->order->shipping_address . ', ' . $this->order->city . ', ' . $this->order->state . ' – ' . $this->order->pincode,
                'total' => (float) $this->order->total,
                'date' => $this->order->created_at?->format('d M Y'),
            ],
        );
    }

    private function resolveTrackingUrl(): string
    {
        $awb = trim((string) $this->awbCode);
        $courier = strtolower(trim((string) $this->courierName));

        if ($awb !== '') {
            if (str_contains($courier, 'delhivery')) {
                return 'https://www.delhivery.com/track/package/' . urlencode($awb);
            }
            if (str_contains($courier, 'shiprocket')) {
                return 'https://shiprocket.co/tracking/' . urlencode($awb);
            }
            if (str_contains($courier, 'blue dart') || str_contains($courier, 'bluedart')) {
                return 'https://www.bluedart.com/tracking';
            }
            if (str_contains($courier, 'dtdc')) {
                return 'https://www.dtdc.in/tracking.asp';
            }
        }

        return url('/my-orders');
    }
}
