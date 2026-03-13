<template>
    <div class="form-card" style="padding: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <h2 style="margin-bottom: 0;">Metrics Dashboard</h2>
            <div style="margin-top: 10px;">
                <strong>Range:</strong>
                <a
                    v-for="option in rangeOptions"
                    :key="option"
                    href="#"
                    @click.prevent="setRange(option)"
                    :style="linkStyle(option)"
                >
                    {{ option }}
                </a>
            </div>
        </div>

        <div v-if="error" style="margin-top: 10px; color: #b91c1c;">
            {{ error }}
        </div>

        <div v-if="loading" style="margin-top: 10px;">
            Loading metrics...
        </div>

        <div v-if="dashboard.range" style="margin-top: 6px; color: #6b7280;">
            {{ dashboard.range.start }} → {{ dashboard.range.end }}
        </div>
        <div v-if="dashboard.kpis" style="display: flex; flex-wrap: wrap; gap: 16px; margin-top: 20px; width: 100%;">
            <div class="form-field" style="border: 1px solid #e5e7eb; padding: 12px 16px; flex: 1 1 calc(50% - 16px); min-width: 220px;">
                <div style="font-size: 12px; color: #6b7280;">Avg Latency</div>
                <div style="font-size: 22px; font-weight: bold;">{{ dashboard.kpis.avg_latency_ms }} ms</div>
            </div>
            <div class="form-field" style="border: 1px solid #e5e7eb; padding: 12px 16px; flex: 1 1 calc(50% - 16px); min-width: 220px;">
                <div style="font-size: 12px; color: #6b7280;">Error Rate</div>
                <div style="font-size: 22px; font-weight: bold;">{{ dashboard.kpis.error_rate_pct }}%</div>
            </div>
            <div class="form-field" style="border: 1px solid #e5e7eb; padding: 12px 16px; flex: 1 1 calc(50% - 16px); min-width: 220px;">
                <div style="font-size: 12px; color: #6b7280;">Queue Failure Rate</div>
                <div style="font-size: 22px; font-weight: bold;">{{ dashboard.kpis.queue_failure_rate_pct }}%</div>
            </div>
            <div class="form-field" style="border: 1px solid #e5e7eb; padding: 12px 16px; flex: 1 1 calc(50% - 16px); min-width: 220px;">
                <div style="font-size: 12px; color: #6b7280;">Total Requests</div>
                <div style="font-size: 22px; font-weight: bold;">{{ dashboard.kpis.total_requests }}</div>
            </div>
        </div>
        <div style="margin-top: 20px;">
            <h3>HTTP Requests</h3>
            <div v-if="hasHttp" style="display: flex; flex-wrap: wrap; gap: 20px;">
                <div style="flex: 1 1 380px; max-width: 48%;">
                    <canvas ref="httpLatencyChart" height="90"></canvas>
                </div>
                <div style="flex: 1 1 380px; max-width: 48%;">
                    <canvas ref="httpTotalChart" height="90"></canvas>
                </div>
                <div v-if="statusTotals.length" style="margin-top: 15px;">
                    <strong>Top Status Codes</strong>
                    <ul>
                        <li v-for="item in statusTotals" :key="item.key">{{ item.key }}: {{ item.count }}</li>
                    </ul>
                </div>
            </div>
            <p v-else>No HTTP metrics captured yet.</p>
        </div>

        <div style="margin-top: 30px;">
            <h3>Database</h3>
            <div v-if="hasDb" style="display: flex; flex-wrap: wrap; gap: 20px;">
                <div style="flex: 1 1 380px; max-width: 48%;">
                    <canvas ref="dbTimeChart" height="90"></canvas>
                </div>
                <div style="flex: 1 1 380px; max-width: 48%;">
                    <canvas ref="dbCountChart" height="90"></canvas>
                </div>
            </div>
            <p v-else>No DB metrics captured yet.</p>
        </div>

        <div style="margin-top: 30px;">
            <h3>Queue</h3>
            <div v-if="hasQueue" style="max-width: 520px;">
                <canvas ref="queueChart" height="90"></canvas>
            </div>
            <p v-else>No queue metrics captured yet.</p>
        </div>

        <div style="margin-top: 30px;">
            <h3>Exceptions</h3>
            <ul v-if="exceptionsList.length">
                <li v-for="item in exceptionsList" :key="item.key">{{ item.key }}: {{ item.count }}</li>
            </ul>
            <p v-else>No exception metrics captured yet.</p>
        </div>

        <div style="margin-top: 30px;">
            <h3>Slow Routes</h3>
            <ul v-if="slowRoutesList.length">
                <li v-for="item in slowRoutesList" :key="item.route">
                    {{ item.route }}: {{ item.avg }} ms avg
                    <span v-if="item.p95">, p95 {{ item.p95 }} ms</span>
                </li>
            </ul>
            <p v-else>No slow routes captured yet.</p>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        initialRange: {
            type: String,
            default: '24h',
        },
        rangeOptions: {
            type: Array,
            default: () => ['6h', '24h', '7d'],
        },
    },
    data() {
        return {
            range: this.initialRange,
            dashboard: {},
            loading: false,
            error: null,
            charts: {},
        };
    },
    computed: {
        labels() {
            return this.dashboard.labels || [];
        },
        hasHttp() {
            return this.dashboard.http && this.dashboard.http.latency;
        },
        hasDb() {
            return this.dashboard.db && this.dashboard.db.totalTime;
        },
        hasQueue() {
            return this.dashboard.queue && this.dashboard.queue.processed;
        },
        statusTotals() {
            const totals = this.dashboard.http ? this.dashboard.http.statusTotals || {} : {};
            return Object.keys(totals).map((key) => ({ key, count: totals[key] }));
        },
        exceptionsList() {
            const totals = this.dashboard.exceptions || {};
            return Object.keys(totals).map((key) => ({ key, count: totals[key] }));
        },
        slowRoutesList() {
            return this.dashboard.slow_routes || [];
        },
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        linkStyle(option) {
            return `margin-left: 10px; ${this.range === option ? 'font-weight: bold;' : ''}`;
        },
        setRange(option) {
            if (this.range === option) return;
            this.range = option;
            this.fetchData();
        },
        fetchData() {
            this.loading = true;
            this.error = null;

            fetch(`/metrics-features/data?range=${encodeURIComponent(this.range)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Failed to load metrics data.');
                    }
                    return response.json();
                })
                .then((data) => {
                    this.dashboard = data || {};
                    this.$nextTick(() => {
                        this.renderCharts();
                    });
                })
                .catch((error) => {
                    this.error = error.message || 'Failed to load metrics data.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        renderCharts() {
            this.destroyCharts();
            if (!window.Chart) {
                this.error = 'Chart.js is not loaded.';
                return;
            }

            if (this.hasHttp) {
                this.charts.httpLatency = this.buildLineChart(
                    this.$refs.httpLatencyChart,
                    'Avg Request Latency (ms)',
                    this.dashboard.http.latency,
                    '#f59e0b'
                );
                this.charts.httpTotal = this.buildLineChart(
                    this.$refs.httpTotalChart,
                    'Total Requests',
                    this.dashboard.http.total,
                    '#2563eb'
                );
            }

            if (this.hasDb) {
                this.charts.dbTime = this.buildLineChart(
                    this.$refs.dbTimeChart,
                    'DB Total Time (ms)',
                    this.dashboard.db.totalTime,
                    '#10b981'
                );
                this.charts.dbCount = this.buildLineChart(
                    this.$refs.dbCountChart,
                    'DB Query Count',
                    this.dashboard.db.queryCount,
                    '#8b5cf6'
                );
            }

            if (this.hasQueue) {
                this.charts.queue = this.buildBarChart(
                    this.$refs.queueChart,
                    this.labels,
                    [
                        {
                            label: 'Processed',
                            data: this.dashboard.queue.processed,
                            backgroundColor: '#22c55e',
                        },
                        {
                            label: 'Failed',
                            data: this.dashboard.queue.failed,
                            backgroundColor: '#ef4444',
                        },
                    ]
                );
            }
        },
        buildLineChart(el, label, data, color) {
            if (!el) return null;
            return new window.Chart(el, {
                type: 'line',
                data: {
                    labels: this.labels,
                    datasets: [
                        {
                            label,
                            data,
                            borderColor: color,
                            backgroundColor: `${color}33`,
                            tension: 0.3,
                            fill: true,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true },
                    },
                    scales: {
                        y: { beginAtZero: true },
                    },
                },
            });
        },
        buildBarChart(el, labels, datasets) {
            if (!el) return null;
            return new window.Chart(el, {
                type: 'bar',
                data: {
                    labels,
                    datasets,
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true },
                    },
                    scales: {
                        y: { beginAtZero: true },
                    },
                },
            });
        },
        destroyCharts() {
            Object.keys(this.charts).forEach((key) => {
                const chart = this.charts[key];
                if (chart && typeof chart.destroy === 'function') {
                    chart.destroy();
                }
            });
            this.charts = {};
        },
    },
};
</script>
