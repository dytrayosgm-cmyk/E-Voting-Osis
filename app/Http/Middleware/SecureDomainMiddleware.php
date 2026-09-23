<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecureDomainMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $configured = config('app.allowed_hosts', '');
        $allowedHosts = collect(explode(',', $configured))
            ->map(fn ($host) => trim((string) $host))
            ->filter()
            ->map(function (string $host): string {
                $host = preg_replace('#^https?://#i', '', $host);
                $host = rtrim($host, '/');
                $host = parse_url('https://'.$host, PHP_URL_HOST) ?: $host;

                return strtolower($host);
            })
            ->unique()
            ->values()
            ->all();

        $host = strtolower($request->getHost());
        if ($host !== null && $allowedHosts !== [] && ! in_array($host, $allowedHosts, true)) {
            abort(403, 'Akses domain tidak diizinkan.');
        }

        return $next($request);
    }
}
