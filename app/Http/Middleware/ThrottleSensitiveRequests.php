<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Cache;

/**
 * Strict throttle for login, registration and password-reset endpoints.
 *
 * Same as `throttle:<max>,<minutes>,<prefix>` but counts hits in a store that is
 * shared between requests (performance.throttle_store, e.g. "database"). The
 * default cache is per-request on serverless hosts, where a normal throttle
 * never reaches its limit. Always pass a prefix so each route has its own counter.
 */
class ThrottleSensitiveRequests extends ThrottleRequests
{
    public function __construct()
    {
        parent::__construct(static::limiter());
    }

    /**
     * Rate limiter backed by the shared throttle store (also used for per-account login lockout).
     */
    public static function limiter(): RateLimiter
    {
        return new RateLimiter(Cache::store(config('performance.throttle_store')));
    }
}
