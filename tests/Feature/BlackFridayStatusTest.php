<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlackFridayStatusTest extends TestCase
{
    private const PAGE = '/server-admin/black-friday';
    private const STATUS = self::PAGE . '/status';

    public function test_only_super_admin_can_open_the_checker(): void
    {
        $this->get(self::PAGE)->assertRedirect('/login');
        $this->getJson(self::STATUS)->assertStatus(401);
        $this->actingAs($this->user(0))->get(self::PAGE)->assertForbidden();
        $this->getJson(self::STATUS)->assertForbidden();
        $this->actingAs($this->user(1))->get(self::PAGE)->assertOk()->assertSee('Black Friday booking fees');
    }

    public function test_active_check_reads_live_values_without_writing_wordpress_or_the_snapshot(): void
    {
        $this->configurePromotion();
        $this->travelTo(Carbon::parse('2026-11-26 20:01:00', 'Africa/Johannesburg'));
        $this->fakeWordPress(250, '350');
        $snapshot = $this->storeSnapshot('active');

        $this->actingAs($this->user(1))->getJson(self::STATUS)
            ->assertOk()->assertJsonPath('phase', 'promotion')->assertJsonPath('state', 'active')
            ->assertJsonPath('rows.0.result', 'match')
            ->assertJsonPath('rows.1.result', 'mismatch');

        Http::assertSentCount(2);
        Http::assertNotSent(function (Request $request) {
            return $request->method() !== 'GET';
        });
        $this->assertSame($snapshot, Storage::disk('local')->get('promotion-test.json'));
    }

    public function test_post_promotion_compares_against_original_values(): void
    {
        $this->configurePromotion();
        $this->travelTo(Carbon::parse('2026-12-01 00:01:00', 'Africa/Johannesburg'));
        $this->fakeWordPress(500, '350');
        $this->storeSnapshot('restored');

        $this->actingAs($this->user(1))->getJson(self::STATUS)
            ->assertOk()->assertJsonPath('phase', 'rollback')
            ->assertJsonPath('rows.0.expected', 500)->assertJsonPath('rows.0.result', 'match')
            ->assertJsonPath('rows.1.expected', '350')->assertJsonPath('rows.1.result', 'match');
    }

    private function user(int $role): User
    {
        return (new User())->forceFill(['id' => 1, 'role_super' => $role]);
    }

    private function configurePromotion(): void
    {
        Storage::fake('local');
        config([
            'booking_promotions.black_friday_2026.residences' => [10 => 'test-residence'],
            'booking_promotions.black_friday_2026.state_path' => 'promotion-test.json',
            'services.wordpress.url' => 'https://wordpress.test/wp-json/wp/v2',
            'services.wordpress.username' => 'checker',
            'services.wordpress.application_password' => 'test-password',
        ]);
    }

    private function fakeWordPress(int $residenceFee, string $roomFee): void
    {
        Http::fake([
            'wordpress.test/wp-json/wp/v2/residences*' => Http::response([[
                'id' => 10, 'slug' => 'test-residence', 'acf' => ['booking_fee' => $residenceFee],
            ]]),
            'wordpress.test/wp-json/wp/v2/rooms*' => Http::response([[
                'id' => 20, 'acf' => ['residence' => 10, 'booking_fee_override' => $roomFee],
            ]]),
        ]);
    }

    private function storeSnapshot(string $status): string
    {
        $snapshot = json_encode(['status' => $status, 'targets' => [
            ['post_type' => 'residences', 'id' => 10, 'title' => 'Test residence',
                'field' => 'booking_fee', 'original_value' => 500, 'promotion_value' => 250],
            ['post_type' => 'rooms', 'id' => 20, 'title' => 'Room',
                'field' => 'booking_fee_override', 'original_value' => '350', 'promotion_value' => 250],
        ]]);
        Storage::disk('local')->put('promotion-test.json', $snapshot);

        return $snapshot;
    }
}
