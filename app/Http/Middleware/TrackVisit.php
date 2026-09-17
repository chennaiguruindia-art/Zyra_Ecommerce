<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request, $response)) {
            try {
                SiteVisit::trackVisit();
            } catch (\Throwable $e) {
                // Never let analytics break the page.
            }
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if (preg_match('/\.(css|js|png|jpe?g|gif|svg|ico|webp|woff2?|ttf|eot|map)(\?.*)?$/i', $request->path())) {
            return false;
        }

        $ua = strtolower((string) $request->header('User-Agent', ''));
        if ($ua !== '' && preg_match('/bot|crawl|spider|slurp|preview|scrape|monitor|lighthouse|pingdom|uptime|wget|curl|headless/i', $ua)) {
            return false;
        }

        return true;
    }
}