<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * VerifyETagMiddleware: Support ETag for caching and conditional requests
 */
class VerifyETagMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only for GET requests and JSON responses
        if ($request->method() !== 'GET' || !$response->isSuccessful()) {
            return $response;
        }

        $etag = $response->headers->get('ETag');

        // Check If-None-Match header
        if ($request->hasHeader('If-None-Match')) {
            $clientEtag = $request->header('If-None-Match');
            
            if ($clientEtag === $etag) {
                return response('', 304)
                    ->header('ETag', $etag);
            }
        }

        return $response;
    }
}
