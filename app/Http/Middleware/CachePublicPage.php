<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CachePublicPage
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') || $request->user() || $request->query->count() > 0) {
            return $next($request);
        }

        $key = 'page:'.sha1($request->fullUrl());

        if (Cache::has($key)) {
            return response(Cache::get($key));
        }

        $response = $next($request);

        if ($response->isSuccessful() && str_contains((string) $response->headers->get('content-type'), 'text/html')) {
            Cache::put($key, $response->getContent(), now()->addMinutes(30));
        }

        return $response;
    }
}
