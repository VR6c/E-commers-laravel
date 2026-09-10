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

        // Do not cache JSON or API responses here
        $contentType = $response->headers->get('Content-Type', '');
        if (str_contains($contentType, 'application/json')) {
            return $response;
        }

        // Cache headers for storefront guest requests
        $isCustomerAuthed = auth('customer')->check() || auth()->check();
        if (! $isCustomerAuthed) {
            // Enable public browser caching with stale-while-revalidate
            $response->headers->set('Cache-Control', 'public, max-age=120, stale-while-revalidate=300');

            // Generate ETag for 304 Not Modified validation
            $content = $response->getContent();
            if ($content) {
                $etag = md5($content);
                $response->setEtag($etag);

                if ($request->getETags() && in_array($etag, $request->getETags())) {
                    $response->setNotModified();
                    return $response;
                }
            }
        } else {
            $response->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate');
        }

        // Gzip compression for HTML responses if supported by client and not already compressed
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
