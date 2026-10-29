<?php

namespace Tests\Unit;

use App\Models\Residence;
use App\Models\RoomTypes;
use App\Services\PricingInspectorService;
use PHPUnit\Framework\TestCase;

class PricingInspectorServiceTest extends TestCase
{
    public function test_wordpress_room_override_is_used_for_every_supported_funding_type()
    {
        $service = new PricingInspectorService();
        $residence = new Residence(['id' => 5, 'residence' => 'Dunvista Mansions']);
        $roomType = new RoomTypes(['id' => 15, 'name' => 'Single', 'room_map_slug' => '1850', 'pull_price' => 1]);
        $wpData = $this->dunvistaWordPressData();

        foreach (['NSFAS', 'Bursary', 'Cash / Direct', 'GCRA', 'MQA', 'FUNZA', 'NRF'] as $studentType) {
            $result = $service->inspect($residence, $roomType, $studentType, $wpData);
            $this->assertSame(350.0, $result['expected_fee']);
            $this->assertSame(1850, $result['wp_room_id']);
        }
    }

    public function test_missing_wordpress_room_mapping_reports_residence_fee_fallback()
    {
        $service = new PricingInspectorService();
        $residence = new Residence(['id' => 5, 'residence' => 'Dunvista Mansions']);
        $roomType = new RoomTypes(['id' => 15, 'name' => 'Single', 'room_map_slug' => '9999', 'pull_price' => 1]);

        $result = $service->inspect($residence, $roomType, 'Bursary', $this->dunvistaWordPressData());

        $this->assertSame(500.0, $result['expected_fee']);
        $this->assertContains('wp_room_not_found_fallback', $result['warning_codes']);
    }

    private function dunvistaWordPressData(): array
    {
        return [
            'residences' => [[
                'id' => 1633,
                'title' => ['rendered' => 'Dunvista Mansions'],
                'acf' => ['booking_fee' => 500],
            ]],
            'rooms' => [[
                'id' => 1850,
                'title' => ['rendered' => 'Dunvista Single'],
                'acf' => [
                    'residence' => 1633,
                    'booking_fee_override' => '350',
                    'booking_fee_applies_to' => ['NSFAS Students', 'Bursary Students', 'Cash Students'],
                    'price_from' => 5600,
                ],
            ]],
            'errors' => [],
        ];
    }
}
