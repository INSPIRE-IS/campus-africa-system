<?php

namespace App\Listeners;

use App\Services\MetricsCollector;
use Illuminate\Queue\Events\JobFailed;

class MetricsJobFailed
{
    protected $collector;

    public function __construct(MetricsCollector $collector)
    {
        $this->collector = $collector;
    }

    public function handle(JobFailed $event): void
    {
        $this->collector->captureQueueFailed($event);
    }
}
