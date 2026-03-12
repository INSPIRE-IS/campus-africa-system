<?php

namespace App\Console\Commands;

use App\Models\MetricsCapture;
use Illuminate\Console\Command;

class MetricsPrune extends Command
{
    protected $signature = 'metrics:prune {--days=}';
    protected $description = 'Prune metrics captures older than the configured retention window.';

    public function handle()
    {
        $days = $this->option('days');
        if (!is_numeric($days) || (int) $days <= 0) {
            $days = (int) config('metrics.retention_days', 40);
        }

        $cutoff = now()->subDays((int) $days);

        $deleted = MetricsCapture::where('bucket_start', '<', $cutoff)->delete();

        $this->info('Deleted ' . $deleted . ' metrics capture rows older than ' . $cutoff->toDateTimeString() . '.');

        return 0;
    }
}
