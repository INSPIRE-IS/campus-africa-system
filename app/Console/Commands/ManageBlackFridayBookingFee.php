<?php

namespace App\Console\Commands;

use App\Services\WordPressBookingFeePromotionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ManageBlackFridayBookingFee extends Command
{
    protected $signature = 'wordpress:black-friday-booking-fee
        {action=status : dry-run, status, activate, restore, reconcile, test-schedule, test-status, test-restore, or reconcile-test}
        {--minutes=3 : Number of minutes the single-room test remains active}';

    protected $description = 'Safely preview, apply, reconcile, or restore the 2026 WordPress booking-fee promotion.';

    public function handle(WordPressBookingFeePromotionService $promotion)
    {
        $action = strtolower((string) $this->argument('action'));
        $allowed = ['dry-run', 'status', 'activate', 'restore', 'reconcile', 'test-schedule', 'test-status', 'test-restore', 'reconcile-test'];

        if (!in_array($action, $allowed, true)) {
            $this->error('Unknown action. Use: ' . implode(', ', $allowed) . '.');
            return 1;
        }

        $lock = Cache::lock('wordpress:black-friday-booking-fee', 300);
        if (!$lock->get()) {
            $this->warn('Another Black Friday booking-fee command is already running.');
            return 1;
        }

        try {
            if ($action === 'dry-run') {
                $snapshot = $promotion->preview();
                $this->showSummary($snapshot);
                $this->info('Dry run complete. WordPress and the promotion state file were not changed.');
                return 0;
            }

            if ($action === 'status') {
                $status = $promotion->status();
                $this->showConfiguration($promotion->configuration(), $status['state']);
                $this->table(['Type', 'ID', 'Name', 'Field', 'Current', 'Promotion'], array_map(function ($row) {
                    return [$row['type'], $row['id'], $row['name'], $row['field'], $this->display($row['current']), $this->display($row['promotion'])];
                }, $status['rows']));
                return 0;
            }

            if ($action === 'activate') {
                $state = $promotion->activate();
                $this->info('Promotion active. State: ' . $state['status'] . '.');
                return 0;
            }

            if ($action === 'restore') {
                $state = $promotion->restore();
                $this->info('Promotion restore complete. State: ' . $state['status'] . '.');
                return 0;
            }

            if ($action === 'test-schedule') {
                $state = $promotion->scheduleRoomTest($this->option('minutes'));
                $this->info('Room test scheduled for WordPress room ' . $state['target']['id'] . '.');
                $this->line('Starts: ' . $state['starts_at']);
                $this->line('Restores: ' . $state['ends_at']);
                $this->line('Original override: ' . $this->display($state['target']['original_value']));
                $this->line('Test override: ' . $this->display($state['target']['promotion_value']));
                return 0;
            }

            if ($action === 'test-status') {
                $status = $promotion->roomTestStatus();
                if (!$status['state']) {
                    $this->info('No single-room test has been scheduled.');
                    return 0;
                }

                $state = $status['state'];
                $this->line('Room: ' . $state['target']['id'] . ' - ' . $state['target']['title']);
                $this->line('Status: ' . $state['status']);
                $this->line('Starts: ' . $state['starts_at']);
                $this->line('Restores: ' . $state['ends_at']);
                $this->line('Original override: ' . $this->display($state['target']['original_value']));
                $this->line('Current override: ' . $this->display($status['current_value']));
                $this->line('Test override: ' . $this->display($state['target']['promotion_value']));
                if (!empty($state['last_error'])) {
                    $this->error('Last error: ' . $state['last_error']);
                }
                return 0;
            }

            if ($action === 'test-restore') {
                $state = $promotion->restoreRoomTest();
                $this->info('Single-room test restored. State: ' . $state['status'] . '.');
                return 0;
            }

            if ($action === 'reconcile-test') {
                $result = $promotion->reconcileRoomTest();
                $state = $result['state'];
                $this->info('Room test reconcile action: ' . $result['action'] . '; state: ' . ($state['status'] ?? 'none') . '.');
                return 0;
            }

            $result = $promotion->reconcile();
            $state = $result['state'];
            $this->info('Reconcile action: ' . $result['action'] . '; state: ' . ($state['status'] ?? 'none') . '.');
            return 0;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            return 1;
        } finally {
            optional($lock)->release();
        }
    }

    private function showSummary(array $snapshot)
    {
        $this->showConfiguration(config('booking_promotions.black_friday_2026'), null);
        $rows = [];

        foreach ($snapshot['targets'] as $target) {
            $rows[] = [
                $target['post_type'],
                $target['id'],
                $target['title'],
                $target['field'],
                $this->display($target['original_value']),
                $this->display($target['promotion_value']),
                $target['requires_change'] ? 'YES' : 'no',
            ];
        }

        $this->table(['Type', 'ID', 'Name', 'Field', 'Current', 'Promotion', 'Write'], $rows);
    }

    private function showConfiguration(array $config, $state)
    {
        $this->line('Promotion: ' . $config['name']);
        $this->line('Window: ' . $config['starts_at'] . ' to ' . $config['ends_at'] . ' ' . $config['timezone']);
        $this->line('Booking fee: R ' . number_format($config['booking_fee'], 2));
        $this->line('Promotion enabled: ' . ($config['enabled'] ? 'yes' : 'no'));
        $this->line('WordPress writes enabled: ' . (config('services.wordpress.writes_enabled') ? 'yes' : 'no'));
        $this->line('Saved state: ' . ($state['status'] ?? 'none'));
    }

    private function display($value)
    {
        if ($value === null) {
            return 'null';
        }

        if ($value === '') {
            return '(blank/inherit)';
        }

        return (string) $value;
    }
}
