<?php

namespace App\Http\Controllers;

use App\Models\MetricsFeature;
use App\Models\MetricsCapture;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MetricsFeatureController extends Controller
{
    protected function guardAdmin()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        if (Auth::user()->role_super != 1) {
            abort(403, 'Unauthorized');
        }

        return null;
    }

    public function index()
    {
        if ($response = $this->guardAdmin()) {
            return $response;
        }

        $range = $this->rangeHours(request('range', '24h'));
        $features = MetricsFeature::orderBy('name')->get();
        $maxEnabled = (int) config('metrics.max_enabled', 5);
        $enabledCount = $features->where('enabled', true)->count();
        $dashboard = $this->buildDashboard($features, $range['hours']);

        return view('admin.metrics_features', [
            'headerTitle' => 'Metrics Features',
            'title' => 'Metrics',
            'features' => $features,
            'enabledFeatures' => $features->where('enabled', true),
            'disabledFeatures' => $features->where('enabled', false),
            'enabledCount' => $enabledCount,
            'maxEnabled' => $maxEnabled,
            'dashboard' => $dashboard,
            'range' => $range['label'],
            'rangeOptions' => $range['options'],
        ]);
    }

    public function enable(Request $request)
    {
        if ($response = $this->guardAdmin()) {
            return $response;
        }

        $request->validate([
            'feature_id' => 'required|integer|exists:metrics_features,id',
        ]);

        $maxEnabled = (int) config('metrics.max_enabled', 5);
        $enabledCount = MetricsFeature::where('enabled', true)->count();

        if ($enabledCount >= $maxEnabled) {
            return redirect()->back()->with('error', 'Only ' . $maxEnabled . ' features can be enabled at once.');
        }

        $feature = MetricsFeature::find($request->feature_id);
        if ($feature && !$feature->enabled) {
            $feature->enabled = true;
            $feature->save();
        }

        return redirect()->back()->with('success', 'Feature enabled.');
    }

    public function disable($id)
    {
        if ($response = $this->guardAdmin()) {
            return $response;
        }

        $feature = MetricsFeature::findOrFail($id);
        $feature->enabled = false;
        $feature->save();

        return redirect()->back()->with('success', 'Feature disabled.');
    }

    public function update(Request $request)
    {
        if ($response = $this->guardAdmin()) {
            return $response;
        }

        $rates = $request->input('sample_rate', []);

        foreach ($rates as $id => $rate) {
            if (!is_numeric($rate)) {
                continue;
            }

            $rate = (float) $rate;
            if ($rate <= 0) {
                $rate = 0.01;
            }
            if ($rate > 1) {
                $rate = 1.0;
            }

            $feature = MetricsFeature::find($id);
            if ($feature && $feature->enabled) {
                $feature->sample_rate = $rate;
                $feature->save();
            }
        }

        return redirect()->back()->with('success', 'Sample rates updated.');
    }

    public function data(Request $request)
    {
        if ($response = $this->guardAdmin()) {
            return $response;
        }

        $range = $this->rangeHours($request->input('range', '24h'));
        $features = MetricsFeature::orderBy('name')->get();
        $dashboard = $this->buildDashboard($features, $range['hours']);

        return response()->json($dashboard);
    }

    public function codes(Request $request)
    {
        if ($response = $this->guardAdmin()) {
            return $response;
        }

        $status = $request->input('status', '5xx');
        $mode = $request->input('mode', 'grouped');
        $statusLabel = $this->normalizeStatusFilter($status);

        $feature = MetricsFeature::where('key', 'http_requests')->first();
        [$rangeStart, $rangeEnd] = $this->resolveDateRange($request);

        $captures = collect();
        $lastExceptions = [];
        if ($feature) {
            $captures = MetricsCapture::selectRaw('`key`, SUM(`count`) as total, MAX(bucket_start) as last_seen')
                ->where('metrics_feature_id', $feature->id)
                ->where('bucket', 'hour')
                ->whereBetween('bucket_start', [$rangeStart, $rangeEnd])
                ->where('key', 'like', 'status:' . $statusLabel . '|%')
                ->groupBy('key')
                ->orderByDesc($mode === 'latest' ? 'last_seen' : 'total')
                ->paginate(15)
                ->appends(['status' => $statusLabel, 'mode' => $mode]);

            if ($statusLabel === '5xx' && $captures->count()) {
                $exceptionsFeature = MetricsFeature::where('key', 'exceptions')->first();
                if ($exceptionsFeature) {
                    foreach ($captures as $capture) {
                        $routeKey = $this->extractRouteFromStatusKey($capture->key);
                        if (!$routeKey) {
                            continue;
                        }
                        $latest = MetricsCapture::where('metrics_feature_id', $exceptionsFeature->id)
                            ->where('bucket', 'hour')
                            ->whereBetween('bucket_start', [$rangeStart, $rangeEnd])
                            ->where('key', 'like', 'route|' . $routeKey . '|%')
                            ->orderByDesc('bucket_start')
                            ->first();
                        if ($latest) {
                            $lastExceptions[$routeKey] = [
                                'message' => $latest->meta['message'] ?? null,
                                'class' => $latest->meta['class'] ?? null,
                                'last_seen' => $latest->bucket_start,
                            ];
                        }
                    }
                }
            }
        }

        return view('admin.metrics_codes', [
            'headerTitle' => 'Metrics Codes',
            'title' => 'Metrics Codes',
            'status' => $statusLabel,
            'mode' => $mode,
            'captures' => $captures,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'lastExceptions' => $lastExceptions,
        ]);
    }

    public function exceptions(Request $request)
    {
        if ($response = $this->guardAdmin()) {
            return $response;
        }

        $route = $request->input('route');
        if (!$route) {
            abort(404);
        }

        [$rangeStart, $rangeEnd] = $this->resolveDateRange($request);
        $feature = MetricsFeature::where('key', 'exceptions')->first();
        $captures = collect();

        if ($feature) {
            $captures = MetricsCapture::selectRaw('`key`, SUM(`count`) as total, MAX(bucket_start) as last_seen')
                ->where('metrics_feature_id', $feature->id)
                ->where('bucket', 'hour')
                ->whereBetween('bucket_start', [$rangeStart, $rangeEnd])
                ->where('key', 'like', 'route|' . $route . '|%')
                ->groupBy('key')
                ->orderByDesc('total')
                ->paginate(15)
                ->appends(['route' => $route, 'start' => $rangeStart->format('Y-m-d'), 'end' => $rangeEnd->format('Y-m-d')]);
        }

        return view('admin.metrics_exceptions', [
            'headerTitle' => 'Exception Details',
            'title' => 'Exception Details',
            'route' => $route,
            'captures' => $captures,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
        ]);
    }

    public function codesBreakdown(Request $request)
    {
        if ($response = $this->guardAdmin()) {
            return $response;
        }

        $group = $this->normalizeStatusFilter($request->input('group', '5xx'));
        if (!preg_match('/^[345]xx$/', $group)) {
            $group = '5xx';
        }

        $route = $request->input('route');
        if (!$route) {
            abort(404);
        }

        [$rangeStart, $rangeEnd] = $this->resolveDateRange($request);
        $feature = MetricsFeature::where('key', 'http_requests')->first();
        $captures = collect();

        if ($feature) {
            $raw = MetricsCapture::selectRaw('`key`, SUM(`count`) as total, MAX(bucket_start) as last_seen')
                ->where('metrics_feature_id', $feature->id)
                ->where('bucket', 'hour')
                ->whereBetween('bucket_start', [$rangeStart, $rangeEnd])
                ->where('key', 'like', 'status:%|' . $route)
                ->groupBy('key')
                ->orderByDesc('total')
                ->get();

            $codes = [];
            foreach ($raw as $row) {
                $key = $row->key ?? '';
                if (strpos($key, 'status:') !== 0) {
                    continue;
                }
                $parts = explode('|', $key, 2);
                $statusPart = str_replace('status:', '', $parts[0] ?? '');
                if (!preg_match('/^\d{3}$/', $statusPart)) {
                    continue;
                }
                if (floor(((int) $statusPart) / 100) . 'xx' !== $group) {
                    continue;
                }
                if (!isset($codes[$statusPart])) {
                    $codes[$statusPart] = [
                        'code' => $statusPart,
                        'total' => 0,
                        'last_seen' => $row->last_seen,
                    ];
                }
                $codes[$statusPart]['total'] += (int) $row->total;
                if ($row->last_seen > $codes[$statusPart]['last_seen']) {
                    $codes[$statusPart]['last_seen'] = $row->last_seen;
                }
            }

            $captures = collect(array_values($codes))
                ->sortByDesc('total')
                ->values();

            $captures = new \Illuminate\Pagination\LengthAwarePaginator(
                $captures->forPage((int) $request->input('page', 1), 15),
                $captures->count(),
                15,
                (int) $request->input('page', 1),
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        return view('admin.metrics_codes_breakdown', [
            'headerTitle' => 'Code Breakdown',
            'title' => 'Code Breakdown',
            'group' => $group,
            'route' => $route,
            'captures' => $captures,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
        ]);
    }

    protected function buildDashboard($features, int $hours): array
    {
        $now = now();
        $start = $now->copy()->subHours($hours - 1)->minute(0)->second(0);
        $hourPoints = [];
        for ($i = 0; $i < $hours; $i++) {
            $hourPoints[] = $start->copy()->addHours($i);
        }
        $labels = array_map(function (Carbon $hour) {
            return $hour->format('H:i');
        }, $hourPoints);

        $data = [
            'labels' => $labels,
            'http' => $this->buildHttpSeries($features, $start, $labels),
            'db' => $this->buildDbSeries($features, $start, $labels),
            'queue' => $this->buildQueueSeries($features, $start, $labels),
            'exceptions' => $this->buildExceptionsSeries($features, $start),
            'slow_routes' => $this->buildSlowRoutes($features, $start),
            'range' => [
                'start' => $start->copy()->format('Y-m-d H:i'),
                'end' => $now->copy()->format('Y-m-d H:i'),
            ],
        ];

        $data['kpis'] = $this->buildKpis($data);

        return $data;
    }

    protected function buildHttpSeries($features, Carbon $start, array $labels): array
    {
        $feature = $features->firstWhere('key', 'http_requests');
        if (!$feature) {
            return [];
        }

        $captures = MetricsCapture::where('metrics_feature_id', $feature->id)
            ->where('bucket', 'hour')
            ->where('bucket_start', '>=', $start)
            ->get();

        $latency = $this->seriesFromCaptures($captures, 'latency', $labels, function ($capture) {
            return $capture->count > 0 ? round($capture->sum / $capture->count, 2) : 0;
        });

        $total = $this->seriesFromCaptures($captures, 'all', $labels, function ($capture) {
            return (int) $capture->count;
        });

        $statusTotals = [];
        foreach ($captures as $capture) {
            if ($capture->key && strpos($capture->key, 'status:') === 0) {
                $statusTotals[$capture->key] = ($statusTotals[$capture->key] ?? 0) + (int) $capture->count;
            }
        }

        arsort($statusTotals);
        $statusTotals = array_slice($statusTotals, 0, 5, true);

        return [
            'latency' => $latency,
            'total' => $total,
            'statusTotals' => $statusTotals,
        ];
    }

    protected function buildDbSeries($features, Carbon $start, array $labels): array
    {
        $feature = $features->firstWhere('key', 'db_queries');
        if (!$feature) {
            return [];
        }

        $captures = MetricsCapture::where('metrics_feature_id', $feature->id)
            ->where('bucket', 'hour')
            ->where('bucket_start', '>=', $start)
            ->get();

        $totalTime = $this->seriesFromCaptures($captures, 'total', $labels, function ($capture) {
            return (int) $capture->sum;
        });

        $queryCount = $this->seriesFromCaptures($captures, 'queries', $labels, function ($capture) {
            return (int) $capture->count;
        });

        return [
            'totalTime' => $totalTime,
            'queryCount' => $queryCount,
        ];
    }

    protected function buildQueueSeries($features, Carbon $start, array $labels): array
    {
        $feature = $features->firstWhere('key', 'queue');
        if (!$feature) {
            return [];
        }

        $captures = MetricsCapture::where('metrics_feature_id', $feature->id)
            ->where('bucket', 'hour')
            ->where('bucket_start', '>=', $start)
            ->get();

        $processed = $this->seriesFromCaptures($captures, 'processed', $labels, function ($capture) {
            return (int) $capture->count;
        });
        $failed = $this->seriesFromCaptures($captures, 'failed', $labels, function ($capture) {
            return (int) $capture->count;
        });

        return [
            'processed' => $processed,
            'failed' => $failed,
        ];
    }

    protected function buildExceptionsSeries($features, Carbon $start): array
    {
        $feature = $features->firstWhere('key', 'exceptions');
        if (!$feature) {
            return [];
        }

        $captures = MetricsCapture::where('metrics_feature_id', $feature->id)
            ->where('bucket', 'hour')
            ->where('bucket_start', '>=', $start)
            ->get();

        $totals = [];
        foreach ($captures as $capture) {
            $key = $capture->key ?: 'Unknown';
            $totals[$key] = ($totals[$key] ?? 0) + (int) $capture->count;
        }

        arsort($totals);
        return array_slice($totals, 0, 5, true);
    }

    protected function buildSlowRoutes($features, Carbon $start): array
    {
        $feature = $features->firstWhere('key', 'slow_routes');
        if (!$feature) {
            return [];
        }

        $captures = MetricsCapture::where('metrics_feature_id', $feature->id)
            ->where('bucket', 'hour')
            ->where('bucket_start', '>=', $start)
            ->get();

        $routes = [];
        foreach ($captures as $capture) {
            if (!$capture->key) {
                continue;
            }
            $routes[$capture->key]['sum'] = ($routes[$capture->key]['sum'] ?? 0) + (int) $capture->sum;
            $routes[$capture->key]['count'] = ($routes[$capture->key]['count'] ?? 0) + (int) $capture->count;
        }

        $averages = [];
        foreach ($routes as $key => $values) {
            $averages[$key] = $values['count'] > 0 ? round($values['sum'] / $values['count'], 2) : 0;
        }

        arsort($averages);
        return array_slice($averages, 0, 5, true);
    }

    protected function seriesFromCaptures($captures, string $key, array $labels, callable $value): array
    {
        $map = [];
        foreach ($captures as $capture) {
            if ($capture->key !== $key) {
                continue;
            }
            $label = $capture->bucket_start->format('H:i');
            $map[$label] = $value($capture);
        }

        $series = [];
        foreach ($labels as $label) {
            $series[] = $map[$label] ?? 0;
        }

        return $series;
    }

    protected function buildKpis(array $dashboard): array
    {
        $totalRequests = array_sum($dashboard['http']['total'] ?? []);
        $latencySum = 0;
        $latencyCount = 0;

        if (!empty($dashboard['http']['latency'])) {
            foreach ($dashboard['http']['latency'] as $idx => $avg) {
                $count = $dashboard['http']['total'][$idx] ?? 0;
                $latencySum += $avg * $count;
                $latencyCount += $count;
            }
        }

        $avgLatency = $latencyCount > 0 ? round($latencySum / $latencyCount, 2) : 0;
        $errorCount = array_sum($dashboard['exceptions'] ?? []);
        $errorRate = $totalRequests > 0 ? round(($errorCount / $totalRequests) * 100, 2) : 0;

        $processed = array_sum($dashboard['queue']['processed'] ?? []);
        $failed = array_sum($dashboard['queue']['failed'] ?? []);
        $queueTotal = $processed + $failed;
        $queueFailureRate = $queueTotal > 0 ? round(($failed / $queueTotal) * 100, 2) : 0;

        return [
            'avg_latency_ms' => $avgLatency,
            'error_rate_pct' => $errorRate,
            'queue_failure_rate_pct' => $queueFailureRate,
            'total_requests' => $totalRequests,
        ];
    }

    protected function rangeHours(string $range): array
    {
        $options = [
            '6h' => 6,
            '24h' => 24,
            '7d' => 168,
        ];

        $hours = $options[$range] ?? 24;

        return [
            'label' => $range,
            'hours' => $hours,
            'options' => array_keys($options),
        ];
    }

    protected function resolveDateRange(Request $request): array
    {
        $start = $request->input('start');
        $end = $request->input('end');

        if ($start) {
            $rangeStart = Carbon::parse($start)->startOfDay();
        } else {
            $rangeStart = now()->subDays(7)->startOfDay();
        }

        if ($end) {
            $rangeEnd = Carbon::parse($end)->endOfDay();
        } else {
            $rangeEnd = now()->endOfDay();
        }

        if ($rangeEnd->lessThan($rangeStart)) {
            $rangeEnd = $rangeStart->copy()->endOfDay();
        }

        return [$rangeStart, $rangeEnd];
    }

    protected function extractRouteFromStatusKey(?string $key): ?string
    {
        if (!$key || strpos($key, '|') === false) {
            return null;
        }
        $parts = explode('|', $key, 2);
        return $parts[1] ?? null;
    }

    protected function normalizeStatusFilter($status): string
    {
        $status = is_string($status) ? trim($status) : (string) $status;
        if (preg_match('/^\d{3}$/', $status)) {
            return $status;
        }
        if (preg_match('/^[345]xx$/', $status)) {
            return $status;
        }
        return '5xx';
    }
}
