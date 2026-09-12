<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptimizeResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only optimize successful GET responses
        if ($request->method() !== 'GET' || $response->getStatusCode() !== 200) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type', '');
        $isJson = str_contains($contentType, 'application/json');

        // Cache headers handling
        $isCustomerAuthed = false;
        try {
            if (function_exists('auth') && app()->bound('auth')) {
                $isCustomerAuthed = (bool) (auth('customer')->check() || auth()->check());
            }
        } catch (\Throwable $e) {
            $isCustomerAuthed = false;
        }

        if ($isJson) {
            // If authenticated, always ensure private no-store
            if ($isCustomerAuthed) {
                $response->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate');
            }
        } else {
            // Storefront HTML guest requests
            if (! $isCustomerAuthed) {
                $response->headers->set('Cache-Control', 'public, max-age=120, stale-while-revalidate=300');
            } else {
                $response->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate');
            }
        }

        // Generate ETag for 304 Not Modified validation if response has public caching
        $cacheControl = $response->headers->get('Cache-Control', '');
        if (str_contains($cacheControl, 'public')) {
            $content = $response->getContent();
            if ($content) {
                $etag = md5($content);
                $response->setEtag($etag);

                if ($response->isNotModified($request)) {
                    return $response;
                }
            }
        }

        // Gzip compression for HTML and JSON if supported by client and not already compressed
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (str_contains($acceptEncoding, 'gzip') && function_exists('gzencode') && ! $response->headers->has('Content-Encoding')) {
            $content = $response->getContent();
            if ($content && strlen($content) > 1024) {
                $compressed = gzencode($content, 6);
                if ($compressed !== false) {
                    $response->setContent($compressed);
                    $response->headers->set('Content-Encoding', 'gzip');
                    $response->headers->set('Content-Length', (string) strlen($compressed));
                }
            }
        }

        return $response;
    }
}
