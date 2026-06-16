<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = config('services.customer_api.key');
        $providedKey = $request->bearerToken() ?: $request->header('X-API-KEY');

        if (!$configuredKey) {
            return response()->json([
                'message' => 'Customer API key is not configured.',
            ], 503);
        }

        if (!$providedKey || !hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}
