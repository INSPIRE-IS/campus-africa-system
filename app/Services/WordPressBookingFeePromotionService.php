<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class WordPressBookingFeePromotionService
{
    private $config;
    private $wordpressUrl;
    private $wordpressUsername;
    private $wordpressPassword;

    public function __construct()
    {
        $this->config = config('booking_promotions.black_friday_2026', []);
        $this->wordpressUrl = rtrim((string) config('services.wordpress.url'), '/');
        $this->wordpressUsername = config('services.wordpress.username');
        $this->wordpressPassword = config('services.wordpress.application_password');
    }

    public function configuration()
    {
        return $this->config;
    }

    public function state()
    {
        $path = $this->statePath();

        if (!Storage::disk('local')->exists($path)) {
            return null;
        }

        $state = json_decode(Storage::disk('local')->get($path), true);
        if (!is_array($state)) {
            throw new RuntimeException('The promotion state file is not valid JSON: ' . $path);
        }

        return $state;
    }

    public function preview()
    {
        $inventory = $this->inventory();

        return $this->buildSnapshot($inventory);
    }

    public function status()
    {
        $state = $this->state();
        $inventory = $this->inventory();
        $targetFee = $this->targetFee();
        $rows = [];

        foreach ($inventory['residences'] as $residence) {
            $rows[] = [
                'type' => 'residence',
                'id' => $residence['id'],
                'name' => $residence['title'],
                'field' => 'booking_fee',
                'current' => data_get($residence, 'acf.booking_fee'),
                'promotion' => $targetFee,
            ];
        }

        foreach ($inventory['rooms'] as $room) {
            $override = data_get($room, 'acf.booking_fee_override');
            if (!$this->hasOverride($override)) {
                continue;
            }

            $rows[] = [
                'type' => 'room',
                'id' => $room['id'],
                'name' => $room['title'],
                'field' => 'booking_fee_override',
                'current' => $override,
                'promotion' => $targetFee,
            ];
        }

        return [
            'state' => $state,
            'rows' => $rows,
        ];
    }

    public function verification()
    {
        $checkedAt = $this->now();
        $startsAt = Carbon::parse($this->config['starts_at'], $this->timezone());
        $endsAt = Carbon::parse($this->config['ends_at'], $this->timezone());
        $phase = $checkedAt->lt($startsAt) ? 'upcoming' : ($checkedAt->lt($endsAt) ? 'promotion' : 'rollback');
        $state = $this->state();
        $inventory = $this->inventory();
        $values = $this->currentValues($inventory);
        $targets = $state['targets'] ?? $this->buildSnapshot($inventory)['targets'];
        $canVerify = $phase !== 'upcoming' && ($phase !== 'rollback' || $state !== null);
        $rows = [];

        foreach ($targets as $target) {
            $key = $this->targetKey($target);
            $found = array_key_exists($key, $values);
            $expected = $phase === 'promotion' ? $target['promotion_value'] : $target['original_value'];
            $rows[] = [
                'key' => $key,
                'title' => $target['title'],
                'field' => $target['field'],
                'current' => $values[$key] ?? null,
                'expected' => $canVerify ? $expected : null,
                'result' => !$found ? 'missing' : (!$canVerify ? 'observed'
                    : ($this->valuesMatch($values[$key], $expected) ? 'match' : 'mismatch')),
            ];
        }

        return [
            'starts_at' => $startsAt->toIso8601String(),
            'ends_at' => $endsAt->toIso8601String(),
            'checked_at' => $checkedAt->toIso8601String(),
            'phase' => $phase,
            'state' => $state['status'] ?? null,
            'snapshot' => $state !== null,
            'enabled' => (bool) $this->config['enabled'],
            'writes_enabled' => (bool) config('services.wordpress.writes_enabled'),
            'rows' => $rows,
        ];
    }

    public function activate()
    {
        $this->assertWritesAllowed();
        $state = $this->state();

        if (!$state) {
            $state = $this->buildSnapshot($this->inventory());
            $this->saveState($state);
        }

        if (($state['status'] ?? null) === 'restored') {
            throw new RuntimeException('This promotion has already been restored. The preserved snapshot will not be reused for another activation.');
        }

        $currentValues = $this->currentValues($this->inventory());
        $pendingTargets = array_filter($state['targets'], function ($target) use ($currentValues) {
            if (!$target['requires_change']) {
                return false;
            }

            $key = $this->targetKey($target);
            return !array_key_exists($key, $currentValues)
                || !$this->valuesMatch($currentValues[$key], $target['promotion_value']);
        });

        if (empty($pendingTargets) && ($state['status'] ?? null) === 'active') {
            return $state;
        }

        $state['status'] = 'activating';
        $state['last_attempt_at'] = $this->now()->toIso8601String();
        $this->saveState($state);

        try {
            foreach ($state['targets'] as $index => $target) {
                if (!$target['requires_change']) {
                    continue;
                }

                $key = $this->targetKey($target);
                if (array_key_exists($key, $currentValues)
                    && $this->valuesMatch($currentValues[$key], $target['promotion_value'])) {
                    continue;
                }

                $response = $this->writeField($target, $target['promotion_value']);
                $oldValue = array_key_exists($key, $currentValues) ? $currentValues[$key] : null;
                $this->recordEvent($state, $target, $oldValue, $target['promotion_value'], $response, 'activated');
                $state['targets'][$index]['activated_at'] = $this->now()->toIso8601String();
                $this->saveState($state);
            }

            $state['status'] = 'active';
            $state['activated_at'] = $this->now()->toIso8601String();
            $state['last_error'] = null;
            $this->saveState($state);

            return $state;
        } catch (\Throwable $exception) {
            $state['status'] = 'activation_failed';
            $state['last_error'] = $exception->getMessage();
            $this->saveState($state);
            Log::error('WordPress booking-fee promotion activation failed.', [
                'promotion' => $this->config['name'] ?? 'black_friday_2026',
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function restore()
    {
        $this->assertWritesAllowed();
        $state = $this->state();

        if (!$state) {
            throw new RuntimeException('No promotion snapshot exists. Restore was not attempted.');
        }

        if (($state['status'] ?? null) === 'restored') {
            return $state;
        }


        $currentValues = $this->currentValues($this->inventory());

        $state['status'] = 'restoring';
        $state['last_attempt_at'] = $this->now()->toIso8601String();
        $this->saveState($state);

        try {
            foreach ($state['targets'] as $index => $target) {
                if (!$target['requires_change']) {
                    continue;
                }

                $key = $this->targetKey($target);
                if (array_key_exists($key, $currentValues)
                    && $this->valuesMatch($currentValues[$key], $target['original_value'])) {
                    continue;
                }

                $response = $this->writeField($target, $target['original_value']);
                $oldValue = array_key_exists($key, $currentValues) ? $currentValues[$key] : null;
                $this->recordEvent($state, $target, $oldValue, $target['original_value'], $response, 'restored');
                $state['targets'][$index]['restored_at'] = $this->now()->toIso8601String();
                $this->saveState($state);
            }

            $state['status'] = 'restored';
            $state['restored_at'] = $this->now()->toIso8601String();
            $state['last_error'] = null;
            $this->saveState($state);

            return $state;
        } catch (\Throwable $exception) {
            $state['status'] = 'restore_failed';
            $state['last_error'] = $exception->getMessage();
            $this->saveState($state);
            Log::error('WordPress booking-fee promotion restore failed.', [
                'promotion' => $this->config['name'] ?? 'black_friday_2026',
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function reconcile()
    {
        $now = $this->now();
        $startsAt = Carbon::parse($this->config['starts_at'], $this->timezone());
        $endsAt = Carbon::parse($this->config['ends_at'], $this->timezone());

        if ($now->lt($startsAt)) {
            return ['action' => 'waiting', 'state' => $this->state()];
        }

        if ($now->lt($endsAt)) {
            return ['action' => 'activate', 'state' => $this->activate()];
        }

        $state = $this->state();
        if (!$state) {
            throw new RuntimeException('The promotion window has ended, but no snapshot exists. Nothing was changed.');
        }

        return ['action' => 'restore', 'state' => $this->restore()];
    }

    public function scheduleRoomTest($minutes)
    {
        $this->assertWritesAllowed();
        $minutes = (int) $minutes;
        if ($minutes < 2 || $minutes > 15) {
            throw new RuntimeException('Test duration must be between 2 and 15 minutes.');
        }

        $existing = $this->roomTestState();
        if ($existing && !in_array($existing['status'] ?? null, ['restored', 'cancelled'], true)) {
            throw new RuntimeException('A room test is already scheduled or active. Check test-status first.');
        }

        $roomId = (int) $this->config['test_room_id'];
        $room = $this->fetchRoom($roomId);
        $residenceId = (int) data_get($room, 'acf.residence');
        if (!array_key_exists($residenceId, $this->config['residences'] ?? [])) {
            throw new RuntimeException("WordPress room {$roomId} is not part of an eligible promotion residence.");
        }

        $startsAt = $this->now()->copy()->startOfMinute()->addMinute();
        $endsAt = $startsAt->copy()->addMinutes($minutes);
        $state = [
            'version' => 1,
            'status' => 'scheduled',
            'created_at' => $this->now()->toIso8601String(),
            'starts_at' => $startsAt->toIso8601String(),
            'ends_at' => $endsAt->toIso8601String(),
            'target' => [
                'post_type' => 'rooms',
                'id' => $roomId,
                'residence_id' => $residenceId,
                'slug' => (string) ($room['slug'] ?? ''),
                'title' => html_entity_decode(data_get($room, 'title.rendered', 'Room ' . $roomId)),
                'field' => 'booking_fee_override',
                'original_value' => data_get($room, 'acf.booking_fee_override'),
                'promotion_value' => $this->targetFee(),
            ],
            'events' => [],
            'last_error' => null,
        ];

        $this->saveRoomTestState($state);
        Log::info('WordPress booking-fee room test scheduled.', [
            'room_id' => $roomId,
            'starts_at' => $state['starts_at'],
            'ends_at' => $state['ends_at'],
            'original_value' => $state['target']['original_value'],
            'test_value' => $state['target']['promotion_value'],
        ]);

        return $state;
    }

    public function roomTestStatus()
    {
        $state = $this->roomTestState();
        if (!$state) {
            return ['state' => null, 'current_value' => null];
        }

        $room = $this->fetchRoom((int) $state['target']['id']);

        return [
            'state' => $state,
            'current_value' => data_get($room, 'acf.booking_fee_override'),
        ];
    }

    public function reconcileRoomTest()
    {
        $state = $this->roomTestState();
        if (!$state) {
            return ['action' => 'none', 'state' => null];
        }

        if (($state['status'] ?? null) === 'restored') {
            return ['action' => 'restored', 'state' => $state];
        }

        $this->assertWritesAllowed();
        $now = $this->now();
        $startsAt = Carbon::parse($state['starts_at'], $this->timezone());
        $endsAt = Carbon::parse($state['ends_at'], $this->timezone());

        if ($now->lt($startsAt)) {
            return ['action' => 'waiting', 'state' => $state];
        }

        if ($now->lt($endsAt)) {
            return ['action' => 'activate', 'state' => $this->applyRoomTestValue($state, false)];
        }

        return ['action' => 'restore', 'state' => $this->applyRoomTestValue($state, true)];
    }

    public function restoreRoomTest()
    {
        $state = $this->roomTestState();
        if (!$state) {
            throw new RuntimeException('No room test state exists.');
        }

        $this->assertWritesAllowed();

        return $this->applyRoomTestValue($state, true);
    }

    private function applyRoomTestValue(array $state, $restore)
    {
        $target = $state['target'];
        $desiredValue = $restore ? $target['original_value'] : $target['promotion_value'];
        $room = $this->fetchRoom((int) $target['id']);
        $currentValue = data_get($room, 'acf.booking_fee_override');

        try {
            if (!$this->valuesMatch($currentValue, $desiredValue)) {
                $response = $this->writeField($target, $desiredValue);
                $this->recordEvent(
                    $state,
                    $target,
                    $currentValue,
                    $desiredValue,
                    $response,
                    $restore ? 'test_restored' : 'test_activated'
                );
            }

            $state['status'] = $restore ? 'restored' : 'active';
            $state[$restore ? 'restored_at' : 'activated_at'] = $this->now()->toIso8601String();
            $state['last_error'] = null;
            $this->saveRoomTestState($state);

            return $state;
        } catch (\Throwable $exception) {
            $state['status'] = $restore ? 'restore_failed' : 'activation_failed';
            $state['last_error'] = $exception->getMessage();
            $this->saveRoomTestState($state);
            throw $exception;
        }
    }

    private function roomTestState()
    {
        $path = $this->testStatePath();
        if (!Storage::disk('local')->exists($path)) {
            return null;
        }

        $state = json_decode(Storage::disk('local')->get($path), true);
        if (!is_array($state)) {
            throw new RuntimeException('The room test state file is not valid JSON: ' . $path);
        }

        return $state;
    }

    private function saveRoomTestState(array $state)
    {
        $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || !Storage::disk('local')->put($this->testStatePath(), $json)) {
            throw new RuntimeException('Unable to save the room test state file.');
        }
    }

    private function fetchRoom($roomId)
    {
        $this->assertReadConfiguration();
        $response = $this->request()->get($this->wordpressUrl . '/rooms/' . (int) $roomId);
        $this->assertSuccessful($response, 'read room ' . (int) $roomId);

        if ((int) data_get($response->json(), 'id') !== (int) $roomId) {
            throw new RuntimeException('WordPress returned the wrong room while reading room ' . (int) $roomId . '.');
        }

        return $response->json();
    }

    private function inventory()
    {
        $this->assertReadConfiguration();
        $residenceResponse = $this->request()->get($this->wordpressUrl . '/residences', ['per_page' => 100]);
        $roomResponse = $this->request()->get($this->wordpressUrl . '/rooms', ['per_page' => 100]);

        $this->assertSuccessful($residenceResponse, 'read residences');
        $this->assertSuccessful($roomResponse, 'read rooms');

        $configuredResidences = $this->config['residences'] ?? [];
        $residences = collect($residenceResponse->json())
            ->filter(function ($residence) use ($configuredResidences) {
                return array_key_exists((int) $residence['id'], $configuredResidences);
            })
            ->map(function ($residence) use ($configuredResidences) {
                $id = (int) $residence['id'];
                $actualSlug = (string) ($residence['slug'] ?? '');
                $expectedSlug = (string) $configuredResidences[$id];
                if ($actualSlug !== $expectedSlug) {
                    throw new RuntimeException("WordPress residence {$id} has slug {$actualSlug}; expected {$expectedSlug}.");
                }

                return [
                    'id' => $id,
                    'slug' => $actualSlug,
                    'title' => html_entity_decode(data_get($residence, 'title.rendered', $actualSlug)),
                    'acf' => $residence['acf'] ?? [],
                ];
            })
            ->sortBy('id')
            ->values()
            ->all();

        if (count($residences) !== count($configuredResidences)) {
            $foundIds = array_column($residences, 'id');
            $missingIds = array_values(array_diff(array_map('intval', array_keys($configuredResidences)), $foundIds));
            throw new RuntimeException('WordPress did not return every configured residence. Missing IDs: ' . implode(', ', $missingIds));
        }

        $residenceIds = array_map('intval', array_keys($configuredResidences));
        $rooms = collect($roomResponse->json())
            ->filter(function ($room) use ($residenceIds) {
                return in_array((int) data_get($room, 'acf.residence'), $residenceIds, true);
            })
            ->map(function ($room) {
                return [
                    'id' => (int) $room['id'],
                    'slug' => (string) ($room['slug'] ?? ''),
                    'title' => html_entity_decode(data_get($room, 'title.rendered', 'Room ' . $room['id'])),
                    'acf' => $room['acf'] ?? [],
                ];
            })
            ->sortBy('id')
            ->values()
            ->all();

        return [
            'residences' => $residences,
            'rooms' => $rooms,
        ];
    }

    private function buildSnapshot(array $inventory)
    {
        $targetFee = $this->targetFee();
        $targets = [];

        foreach ($inventory['residences'] as $residence) {
            $original = data_get($residence, 'acf.booking_fee');
            $targets[] = [
                'post_type' => 'residences',
                'id' => $residence['id'],
                'slug' => $residence['slug'],
                'title' => $residence['title'],
                'field' => 'booking_fee',
                'original_value' => $original,
                'promotion_value' => $targetFee,
                'requires_change' => !$this->valuesMatch($original, $targetFee),
                'booking_fee_applies_to' => data_get($residence, 'acf.booking_fee_applies_to'),
            ];
        }

        foreach ($inventory['rooms'] as $room) {
            $original = data_get($room, 'acf.booking_fee_override');
            $hasOverride = $this->hasOverride($original);
            $targets[] = [
                'post_type' => 'rooms',
                'id' => $room['id'],
                'residence_id' => (int) data_get($room, 'acf.residence'),
                'slug' => $room['slug'],
                'title' => $room['title'],
                'field' => 'booking_fee_override',
                'original_value' => $original,
                'promotion_value' => $hasOverride ? $targetFee : $original,
                'requires_change' => $hasOverride && !$this->valuesMatch($original, $targetFee),
                'booking_fee_applies_to' => data_get($room, 'acf.booking_fee_applies_to'),
            ];
        }

        return [
            'version' => 1,
            'promotion' => $this->config['name'],
            'timezone' => $this->timezone(),
            'starts_at' => $this->config['starts_at'],
            'ends_at' => $this->config['ends_at'],
            'target_booking_fee' => $targetFee,
            'status' => 'prepared',
            'created_at' => $this->now()->toIso8601String(),
            'targets' => $targets,
            'events' => [],
            'last_error' => null,
        ];
    }

    private function currentValues(array $inventory)
    {
        $values = [];

        foreach ($inventory['residences'] as $residence) {
            $values['residences:' . $residence['id'] . ':booking_fee'] = data_get($residence, 'acf.booking_fee');
        }

        foreach ($inventory['rooms'] as $room) {
            $values['rooms:' . $room['id'] . ':booking_fee_override'] = data_get($room, 'acf.booking_fee_override');
        }

        return $values;
    }

    private function targetKey(array $target)
    {
        return $target['post_type'] . ':' . $target['id'] . ':' . $target['field'];
    }

    private function writeField(array $target, $value)
    {
        // WordPress exposes room overrides as string|null, while residence fees are numeric.
        $requestValue = $target['field'] === 'booking_fee_override' && $value !== null
            ? (string) $value
            : $value;

        $response = $this->request()->post(
            $this->wordpressUrl . '/' . $target['post_type'] . '/' . $target['id'],
            ['acf' => [$target['field'] => $requestValue]]
        );

        $this->assertSuccessful($response, "update {$target['post_type']} {$target['id']}");

        $returnedId = (int) data_get($response->json(), 'id');
        $returnedValue = data_get($response->json(), 'acf.' . $target['field']);
        if ($returnedId !== (int) $target['id'] || !$this->valuesMatch($returnedValue, $value)) {
            throw new RuntimeException(
                "WordPress did not confirm {$target['post_type']} {$target['id']} {$target['field']}=" . $this->displayValue($value) . '.'
            );
        }

        return $response;
    }

    private function recordEvent(array &$state, array $target, $oldValue, $newValue, Response $response, $result)
    {
        $event = [
            'at' => $this->now()->toIso8601String(),
            'result' => $result,
            'post_type' => $target['post_type'],
            'id' => $target['id'],
            'field' => $target['field'],
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'http_status' => $response->status(),
            'response_id' => data_get($response->json(), 'id'),
            'response_value' => data_get($response->json(), 'acf.' . $target['field']),
        ];

        $state['events'][] = $event;
        Log::info('WordPress booking-fee promotion update confirmed.', $event);
    }

    private function saveState(array $state)
    {
        $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || !Storage::disk('local')->put($this->statePath(), $json)) {
            throw new RuntimeException('Unable to save the promotion state file.');
        }
    }

    private function request()
    {
        return Http::withBasicAuth($this->wordpressUsername, $this->wordpressPassword)
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 500);
    }

    private function assertReadConfiguration()
    {
        if (!$this->wordpressUrl || !$this->wordpressUsername || !$this->wordpressPassword) {
            throw new RuntimeException('WordPress API URL, username, or application password is not configured.');
        }
    }

    private function assertWritesAllowed()
    {
        $this->assertReadConfiguration();

        if (!config('services.wordpress.writes_enabled')) {
            throw new RuntimeException('WordPress API writes are disabled for this environment.');
        }

        if (!($this->config['enabled'] ?? false)) {
            throw new RuntimeException('The Black Friday 2026 promotion is disabled for this environment.');
        }
    }

    private function assertSuccessful(Response $response, $operation)
    {
        if ($response->successful()) {
            return;
        }

        $message = data_get($response->json(), 'message', $response->body());
        throw new RuntimeException("WordPress failed to {$operation} (HTTP {$response->status()}): {$message}");
    }

    private function hasOverride($value)
    {
        return $value !== null && $value !== '';
    }

    private function valuesMatch($actual, $expected)
    {
        if (is_numeric($actual) && is_numeric($expected)) {
            return (float) $actual === (float) $expected;
        }

        return $actual === $expected;
    }

    private function displayValue($value)
    {
        if ($value === null) {
            return 'null';
        }

        if ($value === '') {
            return '(blank)';
        }

        return (string) $value;
    }

    private function targetFee()
    {
        return (int) $this->config['booking_fee'];
    }

    private function timezone()
    {
        return $this->config['timezone'] ?? config('app.timezone');
    }

    private function now()
    {
        return Carbon::now($this->timezone());
    }

    private function statePath()
    {
        return $this->config['state_path'];
    }

    private function testStatePath()
    {
        return $this->config['test_state_path'];
    }
}
