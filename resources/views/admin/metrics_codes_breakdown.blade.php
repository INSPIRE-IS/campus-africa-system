@extends('layouts.admin')

@section('content')
    <div class="container">
        <br />
        <h2>{{ strtoupper($group) }} Codes for {{ $route }}</h2>
        <p style="color: #6b7280;">
            Showing grouped codes for {{ $rangeStart->format('Y-m-d') }} → {{ $rangeEnd->format('Y-m-d') }}.
        </p>

        <div style="margin-bottom: 20px;">
            <a href="/metrics-codes?status={{ $group }}&start={{ $rangeStart->format('Y-m-d') }}&end={{ $rangeEnd->format('Y-m-d') }}&mode=grouped" class="detailsChange">
                Back to {{ strtoupper($group) }}
            </a>
        </div>

        <form method="GET" action="/metrics-codes/breakdown" style="margin-bottom: 20px;">
            <input type="hidden" name="group" value="{{ $group }}">
            <input type="hidden" name="route" value="{{ $route }}">
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

        <table class="display" style="width: 100%;">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Total</th>
                    <th>Last Seen</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($captures as $capture)
                    <tr>
                        <td>{{ $capture->code }}</td>
                        <td>{{ $capture->total }}</td>
                        <td>{{ $capture->last_seen }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No data yet for {{ strtoupper($group) }} codes.</td>
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
