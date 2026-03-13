@extends('layouts.admin')

@section('content')
    <div class="container">
        <br />
        <h2>HTTP {{ $status }} Codes</h2>
        <p style="color: #6b7280;">
            Showing top routes for {{ $rangeStart->format('Y-m-d') }} → {{ $rangeEnd->format('Y-m-d') }}.
        </p>

        <div style="margin-bottom: 20px;">
            <a href="/metrics-codes?status=3xx&start={{ $rangeStart->format('Y-m-d') }}&end={{ $rangeEnd->format('Y-m-d') }}&mode={{ $mode }}" class="detailsChange" style="margin-right: 10px;">View 3xx</a>
            <a href="/metrics-codes?status=4xx&start={{ $rangeStart->format('Y-m-d') }}&end={{ $rangeEnd->format('Y-m-d') }}&mode={{ $mode }}" class="detailsChange" style="margin-right: 10px;">View 4xx</a>
            <a href="/metrics-codes?status=5xx&start={{ $rangeStart->format('Y-m-d') }}&end={{ $rangeEnd->format('Y-m-d') }}&mode={{ $mode }}" class="detailsChange">View 5xx</a>
        </div>

        <form method="GET" action="/metrics-codes" style="margin-bottom: 20px;">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="mode" value="{{ $mode }}">
            <label style="margin-right: 10px;">
                Start
                <input type="date" name="start" value="{{ $rangeStart->format('Y-m-d') }}">
            </label>
            <label style="margin-right: 10px;">
                End
                <input type="date" name="end" value="{{ $rangeEnd->format('Y-m-d') }}">
            </label>
            <button type="submit" class="detailsChange">Apply</button>
        </form>

        <div style="margin-bottom: 20px;">
            <a href="/metrics-codes?status={{ $status }}&start={{ $rangeStart->format('Y-m-d') }}&end={{ $rangeEnd->format('Y-m-d') }}&mode=grouped" class="detailsChange" style="margin-right: 10px;">Grouped</a>
            <a href="/metrics-codes?status={{ $status }}&start={{ $rangeStart->format('Y-m-d') }}&end={{ $rangeEnd->format('Y-m-d') }}&mode=latest" class="detailsChange">Latest</a>
        </div>

        <table class="display" style="width: 100%;">
            <thead>
                <tr>
                    <th>Route</th>
                    <th>Total</th>
                    <th>Last Seen</th>
                    @if ($status === '5xx')
                        <th>Last Exception</th>
                    @endif
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($captures as $capture)
                    @php
                        $key = $capture->key ?? '';
                        $route = $key;
                        if (strpos($key, '|') !== false) {
                            $parts = explode('|', $key, 2);
                            $route = $parts[1] ?? $key;
                        }
                    @endphp
                    @php
                        $lastException = $lastExceptions[$route] ?? null;
                    @endphp
                    <tr>
                        <td>{{ $route }}</td>
                        <td>{{ $capture->total }}</td>
                        <td>{{ $capture->last_seen }}</td>
                        @if ($status === '5xx')
                            <td>
                                @if ($lastException)
                                    <div>{{ $lastException['class'] ?? 'Exception' }}</div>
                                    <small>{{ $lastException['message'] ?? 'No message' }}</small>
                                @else
                                    <small>No exception detail yet.</small>
                                @endif
                            </td>
                        @endif
                        <td>
                            @if (preg_match('/^[34]xx$/', $status))
                                <a href="/metrics-codes/breakdown?group={{ $status }}&route={{ urlencode($route) }}&start={{ $rangeStart->format('Y-m-d') }}&end={{ $rangeEnd->format('Y-m-d') }}" class="detailsChange" style="margin-right: 8px;">View Codes</a>
                            @endif
                            @if ($status === '5xx')
                                <a href="/metrics-codes/exceptions?route={{ urlencode($route) }}&start={{ $rangeStart->format('Y-m-d') }}&end={{ $rangeEnd->format('Y-m-d') }}" class="detailsChange">View Exceptions</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $status === '5xx' ? 5 : 4 }}">No data yet for {{ $status }} codes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($captures && method_exists($captures, 'links'))
            <div style="margin-top: 15px;">
                {{ $captures->links() }}
            </div>
        @endif
        <br />
    </div>
@endsection
