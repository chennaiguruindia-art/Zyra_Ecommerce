<?php

namespace App\Services;

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayService
{
    protected Api $client;

    public function __construct()
    {
        $this->client = new Api(
            (string) config('razorpay.key_id'),
            (string) config('razorpay.key_secret')
        );
    }

    /**
     * Return the configured (live/test) key id for the checkout widget.
     */
    public function keyId(): string
    {
        return (string) config('razorpay.key_id');
    }

    public function currency(): string
    {
        return strtoupper((string) config('razorpay.currency', 'INR'));
    }

    /**
     * Create a Razorpay order for the given amount (in INR).
     */
    public function createOrder(float $amount, string $receipt, array $notes = []): array
    {
        $order = $this->client->order->create([
            'amount' => (int) round($amount * 100),
            'currency' => $this->currency(),
            'receipt' => $receipt,
            'notes' => $notes,
        ]);

        return $order->toArray();
    }

    /**
     * Verify the payment signature returned by the Razorpay checkout.
     *
     * @param array $attributes razorpay_order_id, razorpay_payment_id, razorpay_signature
     */
    public function verifySignature(array $attributes): bool
    {
        try {
            $this->client->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (SignatureVerificationError $e) {
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Fetch the payment entity to confirm final status (authorized/captured).
     */
    public function fetchPayment(string $paymentId): array
    {
        return $this->client->payment->fetch($paymentId)->toArray();
    }

    /**
     * Capture an authorized payment for the exact order amount.
     */
    public function capturePayment(string $paymentId, int $amount): array
    {
        return $this->client->payment->fetch($paymentId)->capture([
            'amount' => $amount,
        ])->toArray();
    }
}