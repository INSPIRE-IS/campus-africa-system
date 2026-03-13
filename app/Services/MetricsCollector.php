<?php

namespace App\Services;

use App\Models\MetricsCapture;
use App\Models\MetricsFeature;
use Illuminate\Support\Str;
use Throwable;

class MetricsCollector
{
    protected static $featureCache;
    protected static $featureCacheLoadedAt;
    protected static $requestDbTimeMs = 0;
    protected static $requestQueryCount = 0;

    public function startRequest(): void
    {
        self::$requestDbTimeMs = 0;
        self::$requestQueryCount = 0;
    }

    protected function feature(string $key): ?MetricsFeature
    {
        $ttl = (int) config('metrics.cache_ttl_seconds', 30);
        $stale = self::$featureCacheLoadedAt === null || (time() - self::$featureCacheLoadedAt) > $ttl;

        if (self::$featureCache === null || $stale) {
            try {
                self::$featureCache = MetricsFeature::where('enabled', true)->get()->keyBy('key');
                self::$featureCacheLoadedAt = time();
            } catch (Throwable $ignored) {
                return null;
            }
        }

        return self::$featureCache->get($key);
    }

    protected function shouldSample(MetricsFeature $feature): bool
    {
        $rate = (float) $feature->sample_rate;
        if ($rate >= 1) {
            return true;
        }

        return (mt_rand() / mt_getrandmax()) <= $rate;
    }

    protected function bucketStart(string $bucket)
    {
        $now = now();

        if ($bucket === 'hour') {
            return $now->copy()->minute(0)->second(0);
        }

        if ($bucket === 'day') {
            return $now->copy()->startOfDay();
        }

        return $now->copy()->second(0);
    }

    protected function normalizeKey(?string $key): ?string
    {
        if ($key === null) {
            return null;
        }

        return Str::limit($key, 191, '');
    }

    protected function record(
        MetricsFeature $feature,
        string $bucket,
        $bucketStart,
        ?string $key,
        int $count,
        int $sum = 0,
        ?int $min = null,
        ?int $max = null,
        ?array $meta = null
    ): void {
        if (!$this->shouldSample($feature)) {
            return;
        }

        $bucketStart = $bucketStart ?: $this->bucketStart($bucket);
        $key = $this->normalizeKey($key);

        $capture = MetricsCapture::firstOrNew([
            'metrics_feature_id' => $feature->id,
            'bucket' => $bucket,
            'bucket_start' => $bucketStart,
            'key' => $key,
        ]);

        $capture->count = (int) ($capture->count ?? 0) + $count;
        $capture->sum = (int) ($capture->sum ?? 0) + $sum;

        if ($min !== null) {
            $capture->min = $capture->min === null ? $min : min($capture->min, $min);
        }

        if ($max !== null) {
            $capture->max = $capture->max === null ? $max : max($capture->max, $max);
        }

        if ($meta !== null) {
            $capture->meta = array_merge($capture->meta ?? [], $meta);
        }

        $capture->save();
    }

    public function recordCount(string $featureKey, string $key, int $count = 1, string $bucket = 'minute'): void
    {
        $feature = $this->feature($featureKey);
        if (!$feature) {
            return;
        }

        $this->record($feature, $bucket, null, $key, $count);
    }

    public function recordTiming(string $featureKey, string $key, int $durationMs, string $bucket = 'minute'): void
    {
        $feature = $this->feature($featureKey);
        if (!$feature) {
            return;
        }

        $this->record($feature, $bucket, null, $key, 1, $durationMs, $durationMs, $durationMs);
    }

    public function captureRequest($request, $response, int $durationMs): void
    {
        $status = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 200;

        $this->recordTiming('http_requests', 'latency', $durationMs, 'minute');
        $this->recordCount('http_requests', 'all', 1, 'minute');
        $this->recordCount('http_requests', 'status:' . $status, 1, 'minute');
        $this->recordTiming('http_requests', 'latency', $durationMs, 'hour');
        $this->recordCount('http_requests', 'all', 1, 'hour');
        $this->recordCount('http_requests', 'status:' . $status, 1, 'hour');

        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
        $routeUri = $route ? $route->uri() : $request->path();
        $routeKey = $routeName ?: ($request->method() . ' ' . $routeUri);

        if ($status >= 300) {
            $group = floor($status / 100) . 'xx';
            $this->recordCount('http_requests', 'status:' . $group . '|' . $routeKey, 1, 'hour');
            $this->recordCount('http_requests', 'status:' . $status . '|' . $routeKey, 1, 'hour');
        }

        $threshold = (int) config('metrics.slow_route_threshold_ms', 500);
        if ($durationMs >= $threshold) {
            $this->recordTiming('slow_routes', $routeKey, $durationMs, 'hour');
        }
    }

    public function addQueryTime(float $timeMs): void
    {
        self::$requestDbTimeMs += $timeMs;
        self::$requestQueryCount++;
    }

    public function flushRequestDbMetrics(): void
    {
        if (self::$requestQueryCount === 0) {
            return;
        }

        $feature = $this->feature('db_queries');
        if (!$feature) {
            return;
        }

        $this->recordTiming('db_queries', 'total', (int) round(self::$requestDbTimeMs), 'minute');
        $this->recordCount('db_queries', 'queries', self::$requestQueryCount, 'minute');
        $this->recordTiming('db_queries', 'total', (int) round(self::$requestDbTimeMs), 'hour');
        $this->recordCount('db_queries', 'queries', self::$requestQueryCount, 'hour');
    }

    public function captureException(Throwable $e): void
    {
        $feature = $this->feature('exceptions');
        if (!$feature) {
            return;
        }

        if (!$this->shouldSample($feature)) {
            return;
        }

        $key = class_basename($e);
        $message = Str::limit($e->getMessage(), 160, '');
        $route = null;
        try {
            $routeObj = request()->route();
            $routeName = $routeObj ? $routeObj->getName() : null;
            $routeUri = $routeObj ? $routeObj->uri() : request()->path();
            $route = $routeName ?: (request()->method() . ' ' . $routeUri);
        } catch (Throwable $ignored) {
            $route = null;
        }

        $this->record($feature, 'minute', null, $key, 1, 0, null, null, [
            'message' => $message,
            'class' => $key,
            'route' => $route,
        ]);
        $this->record($feature, 'hour', null, $key, 1);
        if ($route) {
            $routeKey = 'route|' . $route . '|' . $key;
            $this->record($feature, 'hour', null, $routeKey, 1, 0, null, null, [
                'message' => $message,
                'class' => $key,
                'route' => $route,
            ]);
        }
    }

    public function captureQueueProcessed($event): void
    {
        $feature = $this->feature('queue');
        if (!$feature) {
            return;
        }

        $jobName = $event->job ? $event->job->resolveName() : 'unknown';

        $this->recordCount('queue', 'processed', 1, 'minute');
        $this->recordCount('queue', 'job:' . $jobName, 1, 'minute');
        $this->recordCount('queue', 'processed', 1, 'hour');
        $this->recordCount('queue', 'job:' . $jobName, 1, 'hour');
    }

    public function captureQueueFailed($event): void
    {
        $feature = $this->feature('queue');
        if (!$feature) {
            return;
        }

        $jobName = $event->job ? $event->job->resolveName() : 'unknown';

        $this->recordCount('queue', 'failed', 1, 'minute');
        $this->recordCount('queue', 'job:' . $jobName, 1, 'minute');
        $this->recordCount('queue', 'failed', 1, 'hour');
        $this->recordCount('queue', 'job:' . $jobName, 1, 'hour');
    }
}
