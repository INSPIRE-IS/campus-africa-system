<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ApplicationYearMoveService;
use Illuminate\Console\Command;

class MoveApplicationYear extends Command
{
    protected $signature = 'application:move-year
        {userId : users.id / students_info.userUid}
        {--from= : Source application year. Defaults to the active Year lookup}
        {--to= : Target application year. Defaults to from-1}
        {--apply : Apply the move. Without this flag the command is dry-run only}
        {--actor-id= : Optional sys admin users.id to record in the audit log when applying}';

    protected $description = 'Move one wrong-year application into the previous year by updating existing rows in place.';

    public function handle(ApplicationYearMoveService $service)
    {
        $userId = (int) $this->argument('userId');
        $fromYear = $this->option('from');
        $toYear = $this->option('to');
        $apply = (bool) $this->option('apply');

        $actor = $this->resolveActor();

        try {
            $result = $apply
                ? $service->apply($userId, $fromYear, $toYear, $actor)
                : $service->preview($userId, $fromYear, $toYear);
        } catch (\Exception $e) {
            $this->error('Move failed: ' . $e->getMessage());
            return 1;
        }

        $this->line($apply ? 'Mode: APPLY' : 'Mode: DRY RUN');
        $this->line("Application year move: {$result['from_year']} -> {$result['to_year']}");
        $this->line("User: {$result['user_id']}");

        if (!empty($result['records']['user'])) {
            $user = $result['records']['user'];
            $this->line('Email: ' . ($user['email'] ?? ''));
        }

        $this->writeRecordSummary($result);
        $this->writeMessages('Blockers', $result['blockers'] ?? [], true);
        $this->writeMessages('Warnings', $result['warnings'] ?? [], false);

        if (!$result['ok']) {
            $this->error('Move blocked. No records changed.');
            return 1;
        }

        if (!$apply) {
            $this->info('Dry run complete. No records changed.');
            $this->line("To apply: php artisan application:move-year {$userId} --from={$result['from_year']} --to={$result['to_year']} --apply");
            return 0;
        }

        $this->info('Move applied.');
        if (!empty($result['audit_id'])) {
            $this->line('Audit ActionList id: ' . $result['audit_id']);
        }

        return 0;
    }

    protected function resolveActor()
    {
        $actorId = (int) $this->option('actor-id');
        if ($actorId <= 0) {
            return [
                'id' => null,
                'display_name' => 'System',
                'role' => null,
            ];
        }

        $actor = User::withTrashed()->where('id', $actorId)->first();
        if (!$actor) {
            return [
                'id' => $actorId,
                'display_name' => 'Unknown sys admin',
                'role' => null,
            ];
        }

        $name = trim(($actor->userFirstName ?? '') . ' ' . ($actor->userLastname ?? ''));
        if ($name === '') {
            $name = $actor->email ?: 'Sys admin';
        }

        return [
            'id' => $actor->id,
            'display_name' => $name,
            'role' => $actor->userRole ?? 'system',
        ];
    }

    protected function writeRecordSummary(array $result)
    {
        $records = $result['records'] ?? [];
        $rows = [];

        if (!empty($records['students_info'])) {
            $student = $records['students_info'];
            $rows[] = [
                'students_info',
                $student['id'] ?? '-',
                $student['year'] ?? '-',
                'bookingId=' . ($student['bookingId'] ?? '-'),
            ];
        }

        if (!empty($records['booking'])) {
            $booking = $records['booking'];
            $rows[] = [
                'bookings_data',
                $booking['id'] ?? '-',
                $booking['year'] ?? '-',
                'code=' . ($booking['new_booking_code'] ?? $booking['booking_code'] ?? '-'),
            ];
        }

        if (!empty($rows)) {
            $this->table(['record', 'id', 'year', 'detail'], $rows);
        }

        if (!empty($records['counts'])) {
            $countRows = [];
            foreach ($records['counts'] as $name => $count) {
                $countRows[] = [$name, is_bool($count) ? ($count ? 'yes' : 'no') : $count];
            }
            $this->table(['count', 'value'], $countRows);
        }

        if (!empty($records['target_existing'])) {
            $targetRows = [];
            foreach ($records['target_existing'] as $name => $count) {
                $targetRows[] = [$name, $count];
            }
            $this->table(['target existing', 'count'], $targetRows);
        }
    }

    protected function writeMessages($title, array $messages, $error)
    {
        if (empty($messages)) {
            return;
        }

        $error ? $this->error($title . ':') : $this->warn($title . ':');

        foreach ($messages as $message) {
            $this->line('- [' . ($message['code'] ?? 'message') . '] ' . ($message['message'] ?? ''));
            if (!empty($message['context'])) {
                $this->line('  ' . json_encode($message['context'], JSON_UNESCAPED_SLASHES));
            }
        }
    }
}
