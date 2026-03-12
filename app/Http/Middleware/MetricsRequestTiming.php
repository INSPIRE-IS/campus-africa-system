<?php

namespace App\Http\Middleware;

use App\Services\MetricsCollector;
use Closure;

class MetricsRequestTiming
{
    protected $collector;

    public function __construct(MetricsCollector $collector)
    {
        $this->collector = $collector;
    }

    public function handle($request, Closure $next)
    {
        $this->collector->startRequest();
        $start = microtime(true);

        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $start) * 1000);
        $this->collector->flushRequestDbMetrics();
        $this->collector->captureRequest($request, $response, $durationMs);

        return $response;
    }
}
