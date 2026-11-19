<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManageBlackFridayBookingFeeCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config([
            'services.wordpress.url' => 'https://wordpress.test/wp-json/wp/v2',
            'services.wordpress.username' => 'api-user',
            'services.wordpress.application_password' => 'api-password',
            'services.wordpress.writes_enabled' => true,
            'booking_promotions.black_friday_2026' => [
                'enabled' => true,
                'name' => 'Black Friday test',
                'timezone' => 'Africa/Johannesburg',
                'starts_at' => '2026-11-26 20:00:00',
                'ends_at' => '2026-12-01 00:00:00',
                'booking_fee' => 250,
                'state_path' => 'wordpress-promotions/test.json',
                'test_state_path' => 'wordpress-promotions/room-test.json',
                'test_room_id' => 4245,
                'residences' => [1592 => '49-jorissen'],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dry_run_reads_targets_without_writing_wordpress_or_state()
    {
        $this->fakeWordPress();

        $this->assertSame(0, Artisan::call('wordpress:black-friday-booking-fee', ['action' => 'dry-run']));

        Storage::disk('local')->assertMissing('wordpress-promotions/test.json');
        Http::assertSentCount(2);
        Http::assertNotSent(function (Request $request) {
            return $request->method() === 'POST';
        });
    }

    public function test_activation_snapshots_updates_and_does_not_repeat_confirmed_writes()
    {
        $wordpress = $this->fakeWordPress();

        $this->assertSame(0, Artisan::call('wordpress:black-friday-booking-fee', ['action' => 'activate']));

        $state = json_decode(Storage::disk('local')->get('wordpress-promotions/test.json'), true);
        $this->assertSame('active', $state['status']);
        $this->assertSame(500, $state['targets'][0]['original_value']);
        $this->assertSame('', $state['targets'][1]['original_value']);
        $this->assertSame('500', $state['targets'][2]['original_value']);
        $this->assertCount(2, $state['events']);
        $this->assertSame(250, $wordpress->residenceFee);
        $this->assertSame('', $wordpress->blankRoomOverride);
        $this->assertSame(250, $wordpress->overrideRoomFee);

        $this->assertSame(0, Artisan::call('wordpress:black-friday-booking-fee', ['action' => 'activate']));

        Http::assertSentCount(8);
        $state = json_decode(Storage::disk('local')->get('wordpress-promotions/test.json'), true);
        $this->assertCount(2, $state['events']);
    }

    public function test_restore_returns_exact_snapshot_values_and_preserves_audit_file()
    {
        $wordpress = $this->fakeWordPress();

        $this->assertSame(0, Artisan::call('wordpress:black-friday-booking-fee', ['action' => 'activate']));
        $this->assertSame(0, Artisan::call('wordpress:black-friday-booking-fee', ['action' => 'restore']));

        $state = json_decode(Storage::disk('local')->get('wordpress-promotions/test.json'), true);
        $this->assertSame('restored', $state['status']);
        $this->assertCount(4, $state['events']);
        $this->assertSame(500, $wordpress->residenceFee);
        $this->assertSame('', $wordpress->blankRoomOverride);
        $this->assertSame('500', $wordpress->overrideRoomFee);
        Storage::disk('local')->assertExists('wordpress-promotions/test.json');
    }

    public function test_writes_require_both_environment_safety_switches()
    {
        $this->fakeWordPress();
        config(['booking_promotions.black_friday_2026.enabled' => false]);

        $this->assertSame(1, Artisan::call('wordpress:black-friday-booking-fee', ['action' => 'activate']));
        Storage::disk('local')->assertMissing('wordpress-promotions/test.json');

        config([
            'booking_promotions.black_friday_2026.enabled' => true,
            'services.wordpress.writes_enabled' => false,
        ]);

        $this->assertSame(1, Artisan::call('wordpress:black-friday-booking-fee', ['action' => 'activate']));
        Storage::disk('local')->assertMissing('wordpress-promotions/test.json');
        Http::assertNothingSent();
    }

    public function test_scheduler_changes_only_the_visible_test_room_and_restores_blank_override()
    {
        Carbon::setTestNow(Carbon::parse('2026-11-19 10:00:20', 'Africa/Johannesburg'));
        $wordpress = $this->fakeWordPress();

        $this->assertSame(0, Artisan::call('wordpress:black-friday-booking-fee', [
            'action' => 'test-schedule',
            '--minutes' => 3,
        ]));

        $state = json_decode(Storage::disk('local')->get('wordpress-promotions/room-test.json'), true);
        $this->assertSame('scheduled', $state['status']);
        $this->assertSame(4245, $state['target']['id']);
        $this->assertSame('', $state['target']['original_value']);
        Storage::disk('local')->assertMissing('wordpress-promotions/test.json');

        Carbon::setTestNow(Carbon::parse('2026-11-19 10:01:00', 'Africa/Johannesburg'));
        $this->assertSame(0, Artisan::call('wordpress:black-friday-booking-fee', ['action' => 'reconcile-test']));
        $this->assertSame(250, $wordpress->testRoomOverride);

        Carbon::setTestNow(Carbon::parse('2026-11-19 10:04:00', 'Africa/Johannesburg'));
        $this->assertSame(0, Artisan::call('wordpress:black-friday-booking-fee', ['action' => 'reconcile-test']));
        $this->assertSame('', $wordpress->testRoomOverride);

        $state = json_decode(Storage::disk('local')->get('wordpress-promotions/room-test.json'), true);
        $this->assertSame('restored', $state['status']);
        $this->assertCount(2, $state['events']);
        Storage::disk('local')->assertMissing('wordpress-promotions/test.json');
    }

    private function fakeWordPress()
    {
        $wordpress = (object) [
            'residenceFee' => 500,
            'blankRoomOverride' => '',
            'overrideRoomFee' => '500',
            'testRoomOverride' => '',
        ];

        Http::fake(function (Request $request) use ($wordpress) {
            if ($request->method() === 'GET' && strpos($request->url(), '/rooms/4245') !== false) {
                return Http::response($this->testRoom($wordpress->testRoomOverride), 200);
            }

            if ($request->method() === 'GET' && strpos($request->url(), '/residences') !== false) {
                return Http::response([$this->residence($wordpress->residenceFee)], 200);
            }

            if ($request->method() === 'GET' && strpos($request->url(), '/rooms') !== false) {
                return Http::response($this->rooms($wordpress), 200);
            }

            $data = $request->data();
            if ($request->method() === 'POST' && strpos($request->url(), '/residences/1592') !== false) {
                $wordpress->residenceFee = $data['acf']['booking_fee'];
                return Http::response($this->residence($wordpress->residenceFee), 200);
            }

            if ($request->method() === 'POST' && strpos($request->url(), '/rooms/2002') !== false) {
                $wordpress->overrideRoomFee = $data['acf']['booking_fee_override'];
                return Http::response($this->rooms($wordpress)[1], 200);
            }

            if ($request->method() === 'POST' && strpos($request->url(), '/rooms/4245') !== false) {
                $wordpress->testRoomOverride = $data['acf']['booking_fee_override'];
                return Http::response($this->testRoom($wordpress->testRoomOverride), 200);
            }

            return Http::response(['message' => 'Unexpected test request'], 500);
        });

        return $wordpress;
    }

    private function residence($bookingFee)
    {
        return [
            'id' => 1592,
            'slug' => '49-jorissen',
            'title' => ['rendered' => 'Amani 49 Jorissen'],
            'acf' => [
                'booking_fee' => $bookingFee,
                'booking_fee_applies_to' => ['NSFAS Students', 'Bursary Students', 'Cash Students'],
            ],
        ];
    }

    private function rooms($wordpress)
    {
        return [
            [
                'id' => 2001,
                'slug' => 'blank-room',
                'title' => ['rendered' => 'Blank override room'],
                'acf' => [
                    'residence' => 1592,
                    'booking_fee_override' => $wordpress->blankRoomOverride,
                    'booking_fee_applies_to' => ['Cash Students'],
                ],
            ],
            [
                'id' => 2002,
                'slug' => 'override-room',
                'title' => ['rendered' => 'Override room'],
                'acf' => [
                    'residence' => 1592,
                    'booking_fee_override' => $wordpress->overrideRoomFee,
                    'booking_fee_applies_to' => ['Cash Students'],
                ],
            ],
        ];
    }

    private function testRoom($override)
    {
        return [
            'id' => 4245,
            'slug' => 'single-en-suite-49-jorissen',
            'title' => ['rendered' => 'Single En-Suite | 49 Jorissen (WITS)'],
            'acf' => [
                'residence' => 1592,
                'booking_fee_override' => $override,
                'booking_fee_applies_to' => ['Cash Students'],
            ],
        ];
    }
}
