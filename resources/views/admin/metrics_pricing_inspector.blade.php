@extends('layouts.admin')

@section('content')
    @php
        $money = function ($value) {
            return $value === null ? '-' : 'R ' . number_format((float) $value, 2, '.', ' ');
        };
    @endphp

    <style>
        .pricing-tools { display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:24px; margin:18px 0 28px; }
        .pricing-tool { border-top:3px solid #e74f26; padding-top:14px; }
        .pricing-tool label { display:block; margin-bottom:6px; font-weight:600; }
        .pricing-tool input, .pricing-tool select { width:100%; max-width:440px; margin-bottom:10px; }
        .pricing-table-wrap { width:100%; overflow-x:auto; margin:12px 0 28px; }
        .pricing-table { width:100%; border-collapse:collapse; background:#fff; }
        .pricing-table th, .pricing-table td { padding:9px 10px; border-bottom:1px solid #ddd; text-align:left; vertical-align:top; white-space:nowrap; }
        .pricing-table th { background:#f2f2f2; }
        .pricing-table .wrap { white-space:normal; min-width:220px; }
        .pricing-ok { color:#24713b; font-weight:700; }
        .pricing-warning { color:#a63c22; font-weight:700; }
        .pricing-notice { padding:10px 12px; border-left:4px solid #e74f26; background:#fff4ef; margin:12px 0; }
        .pricing-meta { color:#666; font-size:12px; }
        @media (max-width: 760px) { .pricing-tools { grid-template-columns:1fr; } }
    </style>

    <div class="container">
        <br>
        <div style="display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap;">
            <h2>Pricing Inspector</h2>
            <a href="/metrics-features" class="detailsChange">Back to Metrics</a>
        </div>

        @if ($globalWaiver)
            <div class="pricing-notice"><strong>Global booking-fee waiver is active.</strong> Expected booking fees are currently R 0.00.</div>
        @endif

        @if ($message)
            <div class="pricing-notice">{{ $message }}</div>
        @endif

        <div class="pricing-tools">
            <section class="pricing-tool">
                <h3>Booking Audit</h3>
                <form method="GET" action="/metrics-pricing">
                    <input type="hidden" name="mode" value="booking">
                    <label for="search">Booking reference, user ID, student number, or email</label>
                    <input id="search" name="search" value="{{ request('search') }}" required>
                    <label for="year">Application year</label>
                    <select id="year" name="year">
                        <option value="">Latest matching year</option>
                        @foreach ($years as $year)
                            <option value="{{ $year->lookup_name }}" @if ((string) request('year') === (string) $year->lookup_name) selected @endif>{{ $year->lookup_name }}</option>
                        @endforeach
                    </select>
                    <br>
                    <button type="submit" class="detailsChange">Inspect Booking</button>
                </form>
            </section>

            <section class="pricing-tool">
                <h3>Residence Matrix</h3>
                <form method="GET" action="/metrics-pricing">
                    <input type="hidden" name="mode" value="matrix">
                    <label for="property_id">Residence</label>
                    <select id="property_id" name="property_id" required>
                        <option value="">Select residence</option>
                        @foreach ($residences as $residence)
                            <option value="{{ $residence->id }}" @if ($selectedPropertyId === (int) $residence->id) selected @endif>{{ $residence->residence }}</option>
                        @endforeach
                    </select>
                    <br>
                    <button type="submit" class="detailsChange">Show Pricing Matrix</button>
                </form>
            </section>
        </div>

        @if ($wpData)
            <p class="pricing-meta">
                WordPress checked {{ $wpData['fetched_at']->format('d M Y H:i:s') }} |
                {{ count($wpData['residences']) }} residences |
                {{ count($wpData['rooms']) }} rooms
            </p>
        @endif

        @if ($audit)
            <h3>Stored vs Expected</h3>
            <div class="pricing-table-wrap">
                <table class="pricing-table">
                    <thead><tr><th>Student</th><th>Booking</th><th>Selection</th><th>Student Type</th><th>Stored</th><th>Expected Now</th><th>Result</th></tr></thead>
                    <tbody>
                        <tr>
                            <td>{{ $audit['student']->userFirstName }} {{ $audit['student']->userLastname }}<br><span class="pricing-meta">User {{ $audit['student']->userUid }}</span></td>
                            <td>{{ $audit['booking']->booking_code }}<br><span class="pricing-meta">{{ $audit['booking']->year }}</span></td>
                            <td>{{ $audit['result']['local_residence_name'] }}<br>{{ $audit['result']['local_room_type_name'] }}</td>
                            <td>{{ $audit['student']->studentType }}</td>
                            <td>{{ $money($audit['stored_fee']) }}<br><span class="pricing-meta">Booking row: {{ $money($audit['booking_room_rate']) }}</span></td>
                            <td>{{ $money($audit['result']['expected_fee']) }}</td>
                            <td>
                                @if ($audit['matches'] === true)
                                    <span class="pricing-ok">Matches</span>
                                @elseif ($audit['matches'] === false)
                                    <span class="pricing-warning">Mismatch</span>
                                @else
                                    <span class="pricing-warning">Cannot compare</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3>Decision Trace</h3>
            <div class="pricing-table-wrap">
                <table class="pricing-table">
                    <tbody>
                        <tr><th>Pricing source</th><td>{{ $audit['result']['source'] }}</td></tr>
                        <tr><th>Local mapping</th><td>Residence {{ $audit['result']['local_residence_id'] }}, room type {{ $audit['result']['local_room_type_id'] }}</td></tr>
                        <tr><th>WordPress mapping</th><td>Residence {{ $audit['result']['wp_residence_id'] ?? '-' }}, room {{ $audit['result']['wp_room_mapping_id'] ?: '-' }} (matched {{ $audit['result']['wp_room_id'] ?? '-' }})</td></tr>
                        <tr><th>Residence base fee</th><td>{{ $money($audit['result']['base_fee']) }}</td></tr>
                        <tr><th>Room override</th><td>{{ $money($audit['result']['override_fee']) }}</td></tr>
                        <tr><th>Applies-to rule</th><td>{{ $audit['result']['student_rule'] ?? 'Unmapped' }}: {{ $audit['result']['eligible'] === null ? '-' : ($audit['result']['eligible'] ? 'Included' : 'Excluded') }}</td></tr>
                        <tr><th>Monthly rental source value</th><td>{{ $money($audit['result']['monthly_rental']) }}</td></tr>
                        <tr><th>Expected booking fee</th><td><strong>{{ $money($audit['result']['expected_fee']) }}</strong></td></tr>
                    </tbody>
                </table>
            </div>

            @if (!empty($audit['result']['warnings']))
                <div class="pricing-notice">
                    @foreach ($audit['result']['warnings'] as $warning)
                        <div>{{ $warning }}</div>
                    @endforeach
                </div>
            @endif
        @endif

        @if ($matrix->isNotEmpty())
            <h3>Pricing Matrix</h3>
            <div class="pricing-table-wrap">
                <table class="pricing-table">
                    <thead>
                        <tr><th>Accommodation</th><th>Student Type</th><th>Expected Fee</th><th>Monthly Rental</th><th>Source</th><th>WP Mapping</th><th class="wrap">Warnings</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($matrix as $row)
                            <tr>
                                <td>{{ $row['local_room_type_name'] }}<br><span class="pricing-meta">Local {{ $row['local_room_type_id'] }}</span></td>
                                <td>{{ $row['student_type'] }}</td>
                                <td><strong>{{ $money($row['expected_fee']) }}</strong><br><span class="pricing-meta">Base {{ $money($row['base_fee']) }} | Override {{ $money($row['override_fee']) }}</span></td>
                                <td>{{ $money($row['monthly_rental']) }}</td>
                                <td>{{ $row['source'] }}</td>
                                <td>{{ $row['wp_room_mapping_id'] ?: '-' }} @if ($row['wp_room_id']) <span class="pricing-ok">matched</span> @else <span class="pricing-warning">not matched</span> @endif</td>
                                <td class="wrap">
                                    @forelse ($row['warnings'] as $warning)
                                        <div class="pricing-warning">{{ $warning }}</div>
                                    @empty
                                        <span class="pricing-ok">No warnings</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
