<?php

namespace App\Services;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderMailService
{
    /**
     * Send order notifications to both buyer and seller.
     */
    public static function sendOrderNotifications(Order $order): void
    {
        $order->loadMissing(['items']);

        // 1. Send confirmation to buyer
        static::sendToBuyer($order);

        // 2. Send notification to seller(s)
        static::sendToSeller($order);
    }

    /**
     * Send confirmation email to the buyer.
     */
    public static function sendToBuyer(Order $order): bool
    {
        $recipient = trim((string) $order->customer_email);
        if ($recipient === '' || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Buyer order confirmation skipped: invalid customer email', [
                'order_number' => $order->order_number,
                'email' => $recipient,
            ]);
            return false;
        }

        try {
            Mail::to($recipient)->send(new OrderConfirmationMail($order, isSellerNotification: false));
            Log::info('Buyer order confirmation email sent successfully', [
                'order_number' => $order->order_number,
                'recipient' => $recipient,
            ]);
            return true;
        } catch (Throwable $e) {
            Log::error('Buyer order confirmation email failed on default mailer', [
                'order_number' => $order->order_number,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);

            try {
                Mail::mailer('smtp_ssl')->to($recipient)->send(new OrderConfirmationMail($order, isSellerNotification: false));
                Log::info('Buyer order confirmation email sent via smtp_ssl fallback', [
                    'order_number' => $order->order_number,
                    'recipient' => $recipient,
                ]);
                return true;
            } catch (Throwable $fallbackError) {
                Log::error('Buyer order confirmation email failed on fallback as well', [
                    'order_number' => $order->order_number,
                    'recipient' => $recipient,
                    'error' => $fallbackError->getMessage(),
                ]);
                return false;
            }
        }
    }

    /**
     * Send notification email to the seller(s).
     */
    public static function sendToSeller(Order $order): bool
    {
        $recipients = static::resolveSellerEmails();
        if (empty($recipients)) {
            Log::warning('Seller order notification skipped: no seller email found', [
                'order_number' => $order->order_number,
            ]);
            return false;
        }

        $allSuccess = true;
        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient)->send(new OrderConfirmationMail($order, isSellerNotification: true));
                Log::info('Seller new order notification email sent successfully', [
                    'order_number' => $order->order_number,
                    'recipient' => $recipient,
                ]);
            } catch (Throwable $e) {
                $allSuccess = false;
                Log::error('Seller new order notification email failed on default mailer', [
                    'order_number' => $order->order_number,
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
                ]);

                try {
                    Mail::mailer('smtp_ssl')->to($recipient)->send(new OrderConfirmationMail($order, isSellerNotification: true));
                    Log::info('Seller new order notification email sent via smtp_ssl fallback', [
                        'order_number' => $order->order_number,
                        'recipient' => $recipient,
                    ]);
                } catch (Throwable $fallbackError) {
                    Log::error('Seller new order notification email failed on fallback as well', [
                        'order_number' => $order->order_number,
                        'recipient' => $recipient,
                        'error' => $fallbackError->getMessage(),
                    ]);
                }
            }
        }

        return $allSuccess;
    }

    /**
     * Resolve all seller recipient emails.
     *
     * Priority:
     * 1. env('ADMIN_ORDER_EMAIL') or env('SELLER_EMAIL')
     * 2. Active users with role 'seller' in database
     * 3. config('mail.from.address')
     *
     * @return list<string>
     */
    public static function resolveSellerEmails(): array
    {
        $emails = [];

        $configured = env('ADMIN_ORDER_EMAIL') ?: env('SELLER_EMAIL');
        if ($configured) {
            foreach (explode(',', (string) $configured) as $e) {
                $trimmed = trim($e);
                if ($trimmed !== '' && filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $trimmed;
                }
            }
        }

        if (empty($emails)) {
            try {
                $sellerUsers = User::where('role', 'seller')->pluck('email')->filter()->all();
                foreach ($sellerUsers as $e) {
                    $trimmed = trim($e);
                    if ($trimmed !== '' && filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                        $emails[] = $trimmed;
                    }
                }
            } catch (Throwable $e) {
                Log::warning('Could not query seller emails from users table: ' . $e->getMessage());
            }
        }

        if (empty($emails)) {
            $from = config('mail.from.address', 'order@shopwithzyra.in');
            if ($from && filter_var($from, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $from;
            }
        }

        return array_values(array_unique($emails));
    }
}
