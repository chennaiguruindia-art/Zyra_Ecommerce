<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Meta Conversions API (server-side events).
 *
 * This replaces the standalone Facebook sample script: events are sent from
 * the server with hashed customer data, and every Purchase carries the same
 * event_id as the browser pixel so Meta deduplicates the two sources.
 * The call never throws – if Meta is unreachable the checkout flow is
 * unaffected and the failure is written to the log.
 */
class MetaConversionsApi
{
    /** Outcome of the most recent track() call, for diagnostics/commands. */
    protected static ?array $lastResult = null;

    /**
     * Result of the last track() call:
     * ['success' => bool, 'status' => int, 'body' => string, 'event' => string]
     */
    public static function lastResult(): ?array
    {
        return self::$lastResult;
    }

    /**
    /**
     * Deterministic event id shared between the pixel (order-success page)
     * and this server-side call, so Meta merges them into one Purchase.
     */
    public static function purchaseEventId(Order $order): string
    {
        return 'zyra-purchase-' . $order->order_number;
    }

    /**
     * Send a Purchase event for a freshly created order.
     */
    public static function purchase(Request $request, Order $order): bool
    {
        $contents = [];
        $contentIds = [];

        foreach ($order->items as $item) {
            $id = (string) ($item->product_id ?? $item->product_name);
            $contentIds[] = $id;
            $contents[] = [
                'id' => $id,
                'quantity' => (int) $item->quantity,
                'item_price' => round((float) $item->price, 2),
            ];
        }

        $nameParts = preg_split('/\s+/', trim((string) $order->customer_name)) ?: [''];

        return self::track($request, 'Purchase', [
            'value' => round((float) $order->total, 2),
            'currency' => 'INR',
            'order_id' => (string) $order->order_number,
            'content_type' => 'product',
            'content_ids' => $contentIds,
            'contents' => $contents,
            'num_items' => (int) $order->items->sum('quantity'),
        ], [
            'em' => $order->customer_email,
            'ph' => $order->customer_phone,
            'fn' => $nameParts[0] ?? '',
            'ln' => implode(' ', array_slice($nameParts, 1)),
            'ct' => $order->city,
            'st' => $order->state,
            'zp' => $order->pincode,
            'country' => 'in',
        ], self::purchaseEventId($order));
    }

    /**
     * Send one event to the Conversions API.
     *
     * @param array $customData Meta custom_data (value, currency, contents, …)
     * @param array $identity   Raw PII to be SHA-256 hashed before sending
     *                          (em, ph, fn, ln, ct, st, zp, country).
     */
    public static function track(
        Request $request,
        string $eventName,
        array $customData = [],
        array $identity = [],
        ?string $eventId = null,
    ): bool {
        $pixelId = (string) config('meta.pixel_id');
        $token = (string) config('meta.access_token');
        self::$lastResult = null;

        if ($token === '' || $pixelId === '') {
            Log::debug("Meta CAPI skipped ({$eventName}): META_CAPI_ACCESS_TOKEN not set.");
            self::$lastResult = ['success' => false, 'status' => 0, 'body' => 'META_CAPI_ACCESS_TOKEN not set', 'event' => $eventName];

            return false;
        }

        try {
            $userData = [
                'client_ip_address' => $request->ip(),
                'client_user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            ];

            // fbp / fbc cookies are written by the Meta pixel and let Meta
            // attribute browser and server events to the same ad click.
            foreach (['fbp', 'fbc'] as $cookie) {
                $value = $request->cookies->get($cookie);
                if (!empty($value)) {
                    $userData[$cookie] = $value;
                }
            }

            foreach ($identity as $key => $value) {
                $hashed = self::hashIdentity($key, (string) $value);
                if ($hashed !== '') {
                    $userData[$key] = $hashed;
                }
            }

            $event = [
                'event_name' => $eventName,
                'event_time' => time(),
                'action_source' => 'website',
                'event_source_url' => self::sourceUrl($request),
                'user_data' => $userData,
                'custom_data' => self::cleanCustomData($customData),
            ];

            if ($eventId !== null && $eventId !== '') {
                $event['event_id'] = $eventId;
            }

            $payload = [
                'data' => [$event],   // asJson() handles encoding — no double-encode
                'access_token' => $token,
            ];

            $testCode = (string) config('meta.test_event_code');
            if ($testCode !== '') {
                $payload['test_event_code'] = $testCode;
            }

            $response = Http::timeout(5)
                ->asJson()
                ->post(
                    sprintf('https://graph.facebook.com/%s/%s/events', config('meta.api_version'), $pixelId),
                    $payload
                );

            if ($response->failed()) {
                Log::warning('Meta CAPI request failed', [
                    'event' => $eventName,
                    'status' => $response->status(),
                    'body' => mb_substr((string) $response->body(), 0, 500),
                ]);
                self::$lastResult = ['success' => false, 'status' => $response->status(), 'body' => (string) $response->body(), 'event' => $eventName];

                return false;
            }

            self::$lastResult = ['success' => true, 'status' => $response->status(), 'body' => (string) $response->body(), 'event' => $eventName];

            return true;
        } catch (\Throwable $e) {
            // Never let tracking break checkout.
            Log::warning('Meta CAPI error', ['event' => $eventName, 'error' => $e->getMessage()]);
            self::$lastResult = ['success' => false, 'status' => 0, 'body' => $e->getMessage(), 'event' => $eventName];

            return false;
        }
    }

    /**
     * SHA-256 hash with Meta's field-specific normalization:
     * - email: trimmed + lowercased
     * - phone: digits only, 10-digit Indian numbers expanded to +91
     * - text:  lowercased, punctuation removed
     */
    private static function hashIdentity(string $key, string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        switch ($key) {
            case 'em':
                $value = mb_strtolower($value);
                break;

            case 'ph':
                $value = preg_replace('/\D+/', '', $value) ?? '';
                if (strlen($value) === 10) {
                    $value = '91' . $value; // ZYRA ships within India
                }
                break;

            case 'country':
                $value = mb_strtolower($value);
                break;

            default: // fn, ln, ct, st, zp and friends
                $value = mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', $value) ?? '');
                break;
        }

        if ($value === '') {
            return '';
        }

        return hash('sha256', $value);
    }

    /** Drop empty entries so Meta never receives blank fields. */
    private static function cleanCustomData(array $customData): array
    {
        return array_filter($customData, static function ($value) {
            if ($value === null || $value === '' || $value === []) {
                return false;
            }
            if (is_array($value)) {
                // keep arrays such as contents even if they hold only zeros
                return count($value) > 0;
            }

            return true;
        });
    }

    /**
     * Prefer the page the shopper was on (checkout), not the XHR endpoint
     * the event was fired from.
     */
    private static function sourceUrl(Request $request): string
    {
        $referer = (string) $request->headers->get('referer', '');

        if ($referer !== '') {
            return mb_substr($referer, 0, 500);
        }

        return mb_substr($request->fullUrl(), 0, 500);
    }
}
