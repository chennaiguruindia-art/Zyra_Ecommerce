<?php

namespace App\Console\Commands;

use App\Mail\AbandonedCartMail;
use App\Models\Cart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RemindAbandonedCarts extends Command
{
    protected $signature = 'carts:remind {--idle=60 : Idle minutes after which a cart qualifies} {--max=100 : Max reminders per run}';

    protected $description = 'Email shoppers who left items in their cart without checking out';

    public function handle(): int
    {
        $idleMinutes = max(1, (int) $this->option('idle'));
        $max = max(1, (int) $this->option('max'));
        $threshold = now()->subMinutes($idleMinutes);

        $carts = Cart::query()
            ->active()
            ->withEmail()
            ->notYetReminded()
            ->idleSince($threshold)
            ->orderByDesc('last_activity_at')
            ->take($max)
            ->get();

        if ($carts->isEmpty()) {
            $this->info('No abandoned carts to remind.');

            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($carts as $cart) {
            try {
                Mail::to($cart->email)->send(new AbandonedCartMail($cart));

                $cart->update([
                    'reminded_at' => now(),
                    'reminder_count' => $cart->reminder_count + 1,
                ]);

                $sent++;
            } catch (\Throwable $e) {
                Log::warning('Abandoned cart reminder failed', [
                    'cart_id' => $cart->id,
                    'email' => $cart->email,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Failed to send to {$cart->email}: {$e->getMessage()}");
            }
        }

        $this->info("Reminded {$sent} user(s).");

        return self::SUCCESS;
    }
}