@extends('layouts.admin')

@section('content')
    <div class="container">
        <br />
        <div style="margin-bottom: 20px;">
            <h2>Metrics Features</h2>
            <p><strong>Enabled:</strong> {{ $enabledCount }} / {{ $maxEnabled }}</p>
        </div>

        @if (session('success'))
            <div style="padding: 10px; border: 1px solid #c1e1c1; background: #e6f7e6; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div style="padding: 10px; border: 1px solid #f5c6cb; background: #f8d7da; margin-bottom: 15px;">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="padding: 10px; border: 1px solid #f5c6cb; background: #f8d7da; margin-bottom: 15px;">
                {{ $errors->first() }}
            </div>
        @endif

        <div style="margin-bottom: 25px;">
            <button type="button" class="detailsChange" id="toggleMetricsConfig" onclick="toggleMetricsConfig()">Configure Metrics</button>
        </div>

        <div id="metricsConfigWrapper" class="form-card" style="display: none; padding: 20px; margin-bottom: 25px;">
            <h2>Configure Metrics</h2>

            <div style="margin-bottom: 25px;">
                <form method="POST" action="/metrics-features/enable">
                    @csrf
                    <label for="feature_id" style="display: block; margin-bottom: 6px;">Enable feature</label>
                    <select name="feature_id" id="feature_id" style="min-width: 240px; margin-right: 10px;">
                        @foreach ($disabledFeatures as $feature)
                            <option value="{{ $feature->id }}">{{ $feature->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="detailsChange" @if ($disabledFeatures->count() === 0) disabled @endif>
                        Enable
                    </button>
                    @if ($disabledFeatures->count() === 0)
                        <span style="margin-left: 10px;">All features are already enabled.</span>
                    @endif
                </form>
            </div>

            <form method="POST" action="/metrics-features/update">
                @csrf
                <table class="display" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <th>Description</th>
                            <th>Key</th>
                            <th>Sample Rate</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enabledFeatures as $feature)
                            <tr>
                                <td>{{ $feature->name }}</td>
                                <td>{{ $feature->description }}</td>
                                <td>{{ $feature->key }}</td>
                                <td>
                                    <input
                                        type="number"
                                        name="sample_rate[{{ $feature->id }}]"
                                        value="{{ $feature->sample_rate }}"
                                        step="0.01"
                                        min="0.01"
                                        max="1"
                                        style="width: 90px;"
                                    />
                                </td>
                                <td>Enabled</td>
                                <td>
                                    <button
                                        type="submit"
                                        formaction="/metrics-features/disable/{{ $feature->id }}"
                                        formmethod="POST"
                                        class="detailsChange"
                                        onclick="return confirm('Disable this feature?');"
                                    >
                                        Disable
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No enabled features yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top: 15px;">
                    <button type="submit" class="detailsChange">Save Sample Rates</button>
                </div>
            </form>
        </div>

        <hr />
        <br />
        <metrics-dashboard
            initial-range="{{ $range }}"
            :range-options='@json($rangeOptions)'
        ></metrics-dashboard>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        console.log('Metrics config script loaded');
        function toggleMetricsConfig() {
            const wrapper = document.getElementById('metricsConfigWrapper');
            if (!wrapper) {
                console.warn('Metrics config toggle: wrapper not found');
                return;
            }
            console.log('Metrics config toggle clicked. Current display:', wrapper.style.display);
            wrapper.style.display = wrapper.style.display === 'none' ? 'block' : 'none';
            console.log('Metrics config toggle updated. New display:', wrapper.style.display);
        }
    </script>
@endpush
