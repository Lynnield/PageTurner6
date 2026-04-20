<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TransformApiData
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Pre-process / Sanitize inputs
        if ($request->isJson()) {
            $input = $request->all();
            array_walk_recursive($input, function (&$value) {
                if (is_string($value)) {
                    $value = trim(strip_tags($value));
                }
            });
            $request->replace($input);
        }

        // 2. Handle request
        $response = $next($request);

        // 3. Post-process response (normalize JSON, camelCase, filter fields, ETag)
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);
            $coreData = $data['data'] ?? $data;

            // Handle camelCase conversion
            $coreData = $this->transformKeys($coreData, fn($key) => Str::camel($key));

            // Field filtering
            if ($request->has('fields')) {
                $fields = explode(',', $request->query('fields'));
                $coreData = $this->filterFields($coreData, $fields);
            }

            // Standardize format
            $normalized = [
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                    'version'   => 'v1',
                ],
                'data' => $coreData,
            ];
            
            // Preserve pagination meta if exists
            if (isset($data['links']) || isset($data['meta'])) {
                $normalized['pagination'] = [
                    'links' => $data['links'] ?? null,
                    'meta'  => $data['meta'] ?? null,
                ];
            }

            if (isset($data['errors'])) {
                $normalized['errors'] = $data['errors'];
            }
            
            $response->setData($normalized);

            // 4. ETag Implementation
            $etag = md5($response->getContent());
            $response->setEtag($etag);
            
            if ($request->getETags()) {
                $requestEtag = str_replace('"', '', $request->getETags()[0]);
                if ($requestEtag === $etag) {
                    $response->setNotModified();
                }
            }
        }

        return $response;
    }

    private function transformKeys($data, callable $callback)
    {
        if (!is_array($data)) {
            return $data;
        }

        $result = [];
        foreach ($data as $key => $value) {
            $newKey = is_string($key) ? $callback($key) : $key;
            $result[$newKey] = is_array($value) ? $this->transformKeys($value, $callback) : $value;
        }

        return $result;
    }

    private function filterFields($data, array $fields)
    {
        if (!is_array($data)) {
            return $data;
        }

        // If sequential array (list of objects)
        if (array_is_list($data)) {
            return array_map(fn($item) => $this->filterFields($item, $fields), $data);
        }

        // If associative array (single object)
        $result = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $fields, true)) {
                $result[$key] = $value;
            }
        }

        return empty($result) ? $data : $result; // fallback if fields don't match
    }
}
