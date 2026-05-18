<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TransformApiResponseMiddleware: Convert response data to camelCase
 * and handle field filtering
 */
class TransformApiResponseMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only transform JSON responses
        if (!$response->isSuccessful() || !str_contains($response->headers->get('Content-Type', ''), 'json')) {
            return $response;
        }

        $data = json_decode($response->getContent(), true);

        if (is_array($data)) {
            // Apply field filtering if requested
            if ($request->has('fields')) {
                $data = $this->filterFields($data, $request->get('fields'));
            }

            // Convert to camelCase
            $data = $this->convertToCamelCase($data);

            // Add ETag if data is stable
            $etag = '"' . md5(json_encode($data)) . '"';
            $response->header('ETag', $etag);

            $response->setContent(json_encode($data));
        }

        return $response;
    }

    /**
     * Convert snake_case keys to camelCase
     */
    private function convertToCamelCase($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $newKey = $this->toCamelCase($key);
                $result[$newKey] = $this->convertToCamelCase($value);
            }
            return $result;
        }

        return $data;
    }

    /**
     * Convert string to camelCase
     */
    private function toCamelCase(string $string): string
    {
        $parts = explode('_', $string);
        $camelCase = array_shift($parts);

        foreach ($parts as $part) {
            $camelCase .= ucfirst($part);
        }

        return $camelCase;
    }

    /**
     * Filter fields based on ?fields=id,title,price
     */
    private function filterFields($data, $fields)
    {
        $fieldList = array_map('trim', explode(',', $fields));

        if (isset($data[0]) && is_array($data[0])) {
            // Array of objects
            return array_map(function ($item) use ($fieldList) {
                return array_intersect_key($item, array_flip($fieldList));
            }, $data);
        }

        // Single object
        if (is_array($data)) {
            return array_intersect_key($data, array_flip($fieldList));
        }

        return $data;
    }
}
