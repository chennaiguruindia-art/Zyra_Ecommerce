<?php

namespace App\Console\Commands;

use App\Support\MetaConversionsApi;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class MetaTestEvent extends Command
{
    protected $signature = 'meta:test
        {--event=Lead : Event name to send}
        {--code= : Meta "Test event code" from Events Manager → Test events}';

    protected $description = 'Send a test event to the Meta Conversions API and print Meta\'s response';

    public function handle(): int
    {
        if ((string) config('meta.access_token') === '') {
            $this->error('META_CAPI_ACCESS_TOKEN is empty in .env');
            $this->line('Generate one: Events Manager → Settings → Conversions API → Generate access token');
            $this->line('Then paste it into .env:  META_CAPI_ACCESS_TOKEN=EAA...');

            return self::FAILURE;
        }

        $code = (string) ($this->option('code') ?: config('meta.test_event_code'));
        if ($code !== '') {
            config(['meta.test_event_code' => $code]);
            $this->info("Sending test event with code: {$code}");
        } else {
            $this->warn('No test code — sending a LIVE event. Open Events Manager → Test events to get a code.');
        }

        // Synthetic browser-ish request so IP / user agent / referer are present.
        $request = Request::create('https://zyralifestyle.in/checkout', 'GET', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) MetaTestCommand/1.0',
        ]);
        $request->headers->set('referer', 'https://zyralifestyle.in/checkout');

        $success = MetaConversionsApi::track(
            $request,
            (string) $this->option('event'),
            ['value' => 1.00, 'currency' => 'INR', 'content_type' => 'product', 'content_ids' => ['test-sku']],
            ['em' => 'test.shopper@zyra.example', 'ph' => '9876543210', 'country' => 'in'],
            'zyra-test-' . time()
        );

        $result = MetaConversionsApi::lastResult() ?? [];

        if ($success) {
            $this->info("✔ Meta accepted the event (HTTP {$result['status']}).");
            $this->line('Check it live: Events Manager → Test events (or Data sources → overview).');
            $this->line('Response: ' . mb_substr((string) ($result['body'] ?? ''), 0, 300));

            return self::SUCCESS;
        }

        $this->error('✘ Meta rejected the event.');
        $this->line('HTTP status: ' . ($result['status'] ?? '?'));
        $this->line('Response: ' . mb_substr((string) ($result['body'] ?? ''), 0, 500));
        $this->newLine();
        $this->line('Common causes: expired/invalid access token, wrong pixel id,');
        $this->line('or the token lacks permission for dataset ' . config('meta.pixel_id') . '.');

        return self::FAILURE;
    }
}
