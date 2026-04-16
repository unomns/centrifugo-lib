<?php

declare(strict_types=1);

namespace Unomns\Centrifugo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateCentrifugoSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('centrifugo.verify_proxy_signature', true)) {
            return $next($request);
        }

        $signature = $request->header('X-Centrifugo-Hmac-Sha256');

        if (!$signature) {
            return response()->json(['error' => 'Missing signature'], 401);
        }

        $expected = hash_hmac('sha256', $request->getContent(), config('centrifugo.proxy_hmac_key', ''));

        if (!hash_equals($expected, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
