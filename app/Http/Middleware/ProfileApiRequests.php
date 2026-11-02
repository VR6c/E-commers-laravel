<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detects slow API requests: logs any request over the threshold together with
 * its query count/time, and warns on likely N+1 patterns. Adds a Server-Timing
 * header outside production so slow endpoints show up in browser dev tools.
 */
class ProfileApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = hrtime(true);
        $queries = 0;
        $queryMs = 0.0;
        $seen = [];

        DB::listen(function ($query) use (&$queries, &$queryMs, &$seen) {
            $queries++;
            $queryMs += $query->time;
            $seen[$query->sql] = ($seen[$query->sql] ?? 0) + 1;
        });

        $response = $next($request);

        $totalMs = (hrtime(true) - $start) / 1e6;
        $slowMs = (int) config('performance.slow_request_ms', 500);
        $maxQueries = (int) config('performance.max_queries_per_request', 25);

        if (! app()->isProduction()) {
            $response->headers->set(
                'Server-Timing',
                sprintf('app;dur=%.1f, db;dur=%.1f;desc="%d queries"', $totalMs, $queryMs, $queries)
            );
        }

        $repeated = array_filter($seen, fn (int $n) => $n >= 5);

        if ($totalMs >= $slowMs || $queries > $maxQueries || $repeated) {
            Log::channel(config('performance.log_channel', 'daily'))->warning('Slow API request', [
                'method' => $request->method(),
                'path' => $request->path(),
                'status' => $response->getStatusCode(),
                'total_ms' => round($totalMs, 1),
                'db_ms' => round($queryMs, 1),
                'queries' => $queries,
                'possible_n_plus_one' => array_map(
                    fn (string $sql, int $n) => "{$n}x {$sql}",
                    array_keys($repeated),
                    $repeated
                ),
            ]);
        }

        return $response;
    }
}
