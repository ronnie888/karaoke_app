<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate Limit YouTube API Middleware
 *
 * Protects YouTube API quota by limiting requests per IP/user.
 * Tracks daily quota usage and prevents excessive API consumption.
 */
class RateLimitYouTubeApi
{
    /**
     * Maximum requests per minute per IP
     */
    private const REQUESTS_PER_MINUTE = 10;

    /**
     * Maximum requests per day per IP
     */
    private const REQUESTS_PER_DAY = 1000;

    /**
     * Daily quota limit (YouTube API units)
     */
    private const DAILY_QUOTA_LIMIT = 10000;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestKey($request);

        // Check per-minute rate limit
        if (RateLimiter::tooManyAttempts($key . ':minute', self::REQUESTS_PER_MINUTE)) {
            return $this->buildRateLimitResponse($request, 'Too many requests. Please slow down.');
        }

        // Check per-day rate limit
        $dailyKey = $key . ':daily';
        $dailyAttempts = Cache::get($dailyKey, 0);

        if ($dailyAttempts >= self::REQUESTS_PER_DAY) {
            return $this->buildRateLimitResponse(
                $request,
                'Daily request limit exceeded. Please try again tomorrow.'
            );
        }

        // Check YouTube API quota
        if ($this->isQuotaExceeded()) {
            return $this->buildRateLimitResponse(
                $request,
                'API quota exceeded. Please try again later.'
            );
        }

        // Increment rate limiters
        RateLimiter::hit($key . ':minute', 60); // 1 minute decay
        Cache::increment($dailyKey, 1);

        // Set daily key expiration if not set
        if (! Cache::has($dailyKey . ':ttl')) {
            $secondsUntilMidnight = (int) now()->endOfDay()->diffInSeconds(now());
            Cache::put($dailyKey . ':ttl', true, $secondsUntilMidnight);
        }

        $response = $next($request);

        // Add rate limit headers
        return $this->addRateLimitHeaders($response, $key);
    }

    /**
     * Resolve the rate limit key for the request.
     */
    private function resolveRequestKey(Request $request): string
    {
        // Use user ID if authenticated, otherwise use IP
        if ($request->user()) {
            return 'youtube_api:user:' . $request->user()->id;
        }

        return 'youtube_api:ip:' . $request->ip();
    }

    /**
     * Check if YouTube API quota is exceeded.
     */
    private function isQuotaExceeded(): bool
    {
        $quotaKey = 'youtube_api:quota:daily';
        $currentQuota = Cache::get($quotaKey, 0);

        return $currentQuota >= self::DAILY_QUOTA_LIMIT;
    }

    /**
     * Build a rate limit exceeded response.
     */
    private function buildRateLimitResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'rate_limit_exceeded',
            ], 429);
        }

        return response()->view('errors.429', ['message' => $message], 429);
    }

    /**
     * Add rate limit headers to the response.
     */
    private function addRateLimitHeaders(Response $response, string $key): Response
    {
        $minuteKey = $key . ':minute';
        $remaining = self::REQUESTS_PER_MINUTE - RateLimiter::attempts($minuteKey);
        $retryAfter = RateLimiter::availableIn($minuteKey);

        $response->headers->set('X-RateLimit-Limit', (string) self::REQUESTS_PER_MINUTE);
        $response->headers->set('X-RateLimit-Remaining', (string) max(0, $remaining));

        if ($remaining <= 0) {
            $response->headers->set('Retry-After', (string) $retryAfter);
            $response->headers->set('X-RateLimit-Reset', (string) (time() + $retryAfter));
        }

        return $response;
    }

    /**
     * Increment the YouTube API quota usage.
     *
     * Called after successful API requests to track quota consumption.
     *
     * @param  int  $cost  The quota cost of the API call (search=100, videos=1)
     */
    public static function incrementQuota(int $cost): void
    {
        $quotaKey = 'youtube_api:quota:daily';

        Cache::increment($quotaKey, $cost);

        // Set expiration to end of day if not set
        if (! Cache::has($quotaKey . ':ttl')) {
            $secondsUntilMidnight = (int) now()->endOfDay()->diffInSeconds(now());
            Cache::put($quotaKey . ':ttl', true, $secondsUntilMidnight);
        }
    }

    /**
     * Get current quota usage.
     */
    public static function getQuotaUsage(): int
    {
        return Cache::get('youtube_api:quota:daily', 0);
    }

    /**
     * Get remaining quota.
     */
    public static function getRemainingQuota(): int
    {
        return max(0, self::DAILY_QUOTA_LIMIT - self::getQuotaUsage());
    }

    /**
     * Reset all rate limits (for testing/admin purposes).
     */
    public static function resetLimits(): void
    {
        Cache::flush();
        RateLimiter::clear('youtube_api');
    }
}
