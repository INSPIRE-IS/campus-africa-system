<?php

namespace App\Listeners;

use App\Services\MetricsCollector;
use Illuminate\Queue\Events\JobProcessed;

class MetricsJobProcessed
{
    protected $collector;

    public function __construct(MetricsCollector $collector)
    {
        $this->collector = $collector;
    }

    public function handle(JobProcessed $event): void
    {
        $this->collector->captureQueueProcessed($event);
    }
}
