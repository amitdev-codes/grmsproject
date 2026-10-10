<?php

namespace Modules\Grievance\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Grievance\Services\GrievanceIntakeSecurityService;
use Symfony\Component\HttpFoundation\Response;

class ProtectPublicGrievanceLodging
{
    public function __construct(protected GrievanceIntakeSecurityService $security) {}

    public function handle(Request $request, Closure $next): Response
    {
        $ip = (string) $request->ip();

        if ($this->security->isBlacklisted($ip)) {
            return response()->json(['message' => 'Grievance lodging is unavailable from this network.'], 403);
        }

        if (! $this->security->isWhitelisted($ip)) {
            $key = 'public-grievance-lodging:'.$ip;
            $limit = max(1, $this->security->settings()->lodging_requests_per_minute);

            if (RateLimiter::tooManyAttempts($key, $limit)) {
                return response()->json([
                    'message' => 'Too many grievance submissions. Please try again later.',
                    'retry_after_seconds' => RateLimiter::availableIn($key),
                ], 429)->header('Retry-After', (string) RateLimiter::availableIn($key));
            }

            RateLimiter::hit($key, 60);
        }

        return $next($request);
    }
}
