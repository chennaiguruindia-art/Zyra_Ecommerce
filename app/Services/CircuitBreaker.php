<?php

namespace App\Services;

use Closure;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Enterprise Circuit Breaker Pattern for External Third-Party Services
 * (Razorpay, Shiprocket, Meta CAPI, SMS/Email Gateways).
 *
 * Prevents cascading service failures and hanging threads under high concurrent load (100+ shoppers).
 */
class CircuitBreaker
{
    public const STATE_CLOSED = 'closed';     // Normal operating state
    public const STATE_OPEN = 'open';         // Service is down, requests fail-fast
    public const STATE_HALF_OPEN = 'half_open'; // Probing with trial requests

    /**
     * Execute an external service call protected by the circuit breaker.
     *
     * @param string $service Service name (e.g. 'razorpay', 'shiprocket')
     * @param callable $action The primary external API call
     * @param callable|null $fallback Graceful fallback if circuit is OPEN or fails
     * @param int $failureThreshold Number of consecutive failures to trip circuit
     * @param int $recoveryTimeSeconds Cool-off period before retrying
     * @return mixed
     * @throws Throwable
     */
    public static function call(
        string $service,
        callable $action,
        ?callable $fallback = null,
        int $failureThreshold = 5,
        int $recoveryTimeSeconds = 30
    ) {
        $state = self::getState($service, $recoveryTimeSeconds);

        if ($state === self::STATE_OPEN) {
            Log::warning("CircuitBreaker [{$service}] is OPEN. Failing fast to protect application performance.");
            if ($fallback !== null) {
                return $fallback(new Exception("External service [{$service}] is temporarily unavailable."));
            }
            throw new Exception("The [{$service}] service is temporarily experiencing high latency. Please retry shortly.");
        }

        try {
            $result = $action();
            self::recordSuccess($service);
            return $result;
        } catch (Throwable $e) {
            self::recordFailure($service, $failureThreshold, $recoveryTimeSeconds);
            Log::error("CircuitBreaker [{$service}] recorded failure: " . $e->getMessage());

            if ($fallback !== null) {
                return $fallback($e);
            }

            throw $e;
        }
    }

    /**
     * Determine current state of the circuit.
     */
    public static function getState(string $service, int $recoveryTimeSeconds = 30): string
    {
        $openUntil = (int) Cache::get("cb:{$service}:open_until", 0);

        if ($openUntil > time()) {
            return self::STATE_OPEN;
        }

        // If it was open and recovery time passed, transition to half-open
        if ($openUntil > 0) {
            return self::STATE_HALF_OPEN;
        }

        return self::STATE_CLOSED;
    }

    /**
     * Record a successful call - resets failure counter and closes circuit.
     */
    public static function recordSuccess(string $service): void
    {
        Cache::forget("cb:{$service}:failures");
        Cache::forget("cb:{$service}:open_until");
    }

    /**
     * Record a failure and trip the circuit OPEN if threshold is crossed.
     */
    public static function recordFailure(string $service, int $failureThreshold = 5, int $recoveryTimeSeconds = 30): void
    {
        $failuresKey = "cb:{$service}:failures";
        $failures = (int) Cache::get($failuresKey, 0) + 1;
        Cache::put($failuresKey, $failures, 60);

        if ($failures >= $failureThreshold) {
            $openUntil = time() + $recoveryTimeSeconds;
            Cache::put("cb:{$service}:open_until", $openUntil, $recoveryTimeSeconds + 10);
            Log::critical("CircuitBreaker [{$service}] TRIPPED OPEN! Will cool off for {$recoveryTimeSeconds}s.");
        }
    }

    /**
     * Check if service is currently available (not OPEN).
     */
    public static function isAvailable(string $service, int $recoveryTimeSeconds = 30): bool
    {
        return self::getState($service, $recoveryTimeSeconds) !== self::STATE_OPEN;
    }

    /**
     * Manually reset the circuit breaker for a service.
     */
    public static function reset(string $service): void
    {
        self::recordSuccess($service);
    }
}
