<?php

namespace App\Services;

use App\Models\Residence;
use App\Models\RoomTypes;
use Illuminate\Support\Facades\Http;
use Throwable;

class PricingInspectorService
{
    private const WP_BASE_URL = 'https://www.campusafrica.co.za/wp-json/wp/v2';

    public function fetchWordPressData(): array
    {
        $result = [
            'residences' => [],
            'rooms' => [],
            'fetched_at' => now(),
            'errors' => [],
            'room_pages' => null,
        ];

        try {
            $residences = Http::timeout(10)->get(self::WP_BASE_URL . '/residences', ['per_page' => 100]);
            if ($residences->successful() && is_array($residences->json())) {
                $result['residences'] = $residences->json();
            } else {
                $result['errors'][] = 'WordPress residences request failed with HTTP ' . $residences->status() . '.';
            }
        } catch (Throwable $exception) {
            $result['errors'][] = 'WordPress residences could not be loaded: ' . $exception->getMessage();
        }

        try {
            $rooms = Http::timeout(10)->get(self::WP_BASE_URL . '/rooms', ['per_page' => 100]);
            if ($rooms->successful() && is_array($rooms->json())) {
                $result['rooms'] = $rooms->json();
                $result['room_pages'] = (int) ($rooms->header('X-WP-TotalPages') ?: 1);
                if ($result['room_pages'] > 1) {
                    $result['errors'][] = 'WordPress has more than 100 rooms. The live booking flow reads only the first page, so some room overrides may be missed.';
                }
            } else {
                $result['errors'][] = 'WordPress rooms request failed with HTTP ' . $rooms->status() . '.';
            }
        } catch (Throwable $exception) {
            $result['errors'][] = 'WordPress rooms could not be loaded: ' . $exception->getMessage();
        }

        return $result;
    }

    public function inspect(
        Residence $residence,
        RoomTypes $roomType,
        string $studentType,
        array $wpData,
        bool $globalWaiver = false
    ): array {
        $warnings = [];
        $warningCodes = [];
        $source = $roomType->pull_price === null ? 'Local room type' : 'WordPress';
        $baseFee = null;
        $overrideFee = null;
        $monthlyRental = null;
        $expectedFee = null;
        $eligible = null;
        $wpResidence = null;
        $wpRoom = null;
        $appliesTo = [];
        $studentRule = $this->studentRule($studentType);

        if ($source === 'Local room type') {
            $baseFee = $this->numberOrNull($roomType->booking_price);
            $monthlyRental = $this->numberOrNull($roomType->rent_price);
            $eligible = $studentRule === null ? null : $this->localFeeApplies($roomType, $studentRule);
            $expectedFee = $eligible === false ? 0.0 : $baseFee;

            if ($studentRule === null) {
                $warnings[] = 'This student type is not mapped to a booking-fee eligibility rule.';
                $warningCodes[] = 'unknown_student_type';
            }
            if ($baseFee === null) {
                $warnings[] = 'The local room type has no booking price.';
                $warningCodes[] = 'missing_local_booking_price';
            }
        } else {
            $wpResidence = $this->findWordPressResidence($residence, $wpData['residences'] ?? []);
            if ($wpResidence) {
                $baseFee = $this->numberOrNull(data_get($wpResidence, 'acf.booking_fee'));
            } else {
                $warnings[] = 'The residence name was not matched in the WordPress response.';
                $warningCodes[] = 'wp_residence_not_found';
            }

            if (empty($roomType->room_map_slug)) {
                $warnings[] = 'The local room type has no WordPress room mapping ID.';
                $warningCodes[] = 'missing_room_mapping';
            } elseif ($wpResidence) {
                $wpRoom = $this->findWordPressRoom($roomType, $wpResidence, $wpData['rooms'] ?? []);
                if ($wpRoom) {
                    $override = data_get($wpRoom, 'acf.booking_fee_override');
                    if ($override !== null && $override !== '') {
                        $overrideFee = $this->numberOrNull($override);
                    }
                    $monthlyRental = $this->numberOrNull(data_get($wpRoom, 'acf.price_from'));
                    $appliesTo = data_get($wpRoom, 'acf.booking_fee_applies_to', []);
                    $appliesTo = is_array($appliesTo) ? $appliesTo : [];
                    $eligible = $studentRule === null ? null : in_array($studentRule, $appliesTo, true);
                    $expectedFee = $overrideFee !== null ? $overrideFee : $baseFee;
                    if ($eligible === false) {
                        $expectedFee = 0.0;
                    }
                } else {
                    $expectedFee = $baseFee;
                    $warnings[] = 'WordPress room override was not matched. The live flow falls back to the residence fee.';
                    $warningCodes[] = 'wp_room_not_found_fallback';
                }
            }

            if (!empty($wpData['errors'])) {
                foreach ($wpData['errors'] as $error) {
                    $warnings[] = $error;
                }
                $warningCodes[] = 'wp_fetch_warning';
            }

            if ($studentRule === null) {
                $warnings[] = 'This student type is not mapped to a WordPress applies-to label.';
                $warningCodes[] = 'unknown_student_type';
            }

            if (!$wpResidence || empty($wpData['rooms'])) {
                $expectedFee = null;
            }
        }

        if ($globalWaiver) {
            $expectedFee = 0.0;
            $warnings[] = 'The global booking-fee waiver is active and overrides the configured price.';
            $warningCodes[] = 'global_waiver';
        }

        return [
            'student_type' => $studentType,
            'student_rule' => $studentRule,
            'source' => $globalWaiver ? 'Global waiver' : $source,
            'expected_fee' => $expectedFee,
            'base_fee' => $baseFee,
            'override_fee' => $overrideFee,
            'monthly_rental' => $monthlyRental,
            'eligible' => $eligible,
            'applies_to' => $appliesTo,
            'local_residence_id' => $residence->id,
            'local_residence_name' => $residence->residence,
            'local_room_type_id' => $roomType->id,
            'local_room_type_name' => $roomType->name,
            'pull_price' => $roomType->pull_price,
            'wp_residence_id' => $wpResidence['id'] ?? null,
            'wp_room_id' => $wpRoom['id'] ?? null,
            'wp_room_mapping_id' => $roomType->room_map_slug,
            'wp_room_title' => data_get($wpRoom, 'title.rendered'),
            'warnings' => array_values(array_unique($warnings)),
            'warning_codes' => array_values(array_unique($warningCodes)),
        ];
    }

    private function findWordPressResidence(Residence $residence, array $wpResidences): ?array
    {
        foreach ($wpResidences as $wpResidence) {
            if ((string) data_get($wpResidence, 'title.rendered') === (string) $residence->residence) {
                return $wpResidence;
            }
        }

        return null;
    }

    private function findWordPressRoom(RoomTypes $roomType, array $wpResidence, array $wpRooms): ?array
    {
        foreach ($wpRooms as $wpRoom) {
            if ((string) data_get($wpRoom, 'acf.residence') === (string) ($wpResidence['id'] ?? null)
                && (string) ($wpRoom['id'] ?? null) === (string) $roomType->room_map_slug) {
                return $wpRoom;
            }
        }

        return null;
    }

    private function studentRule(string $studentType): ?string
    {
        if (in_array($studentType, ['NSFAS', 'NRF', 'FUNZA', 'GCRA', 'MQA'], true)) {
            return 'NSFAS Students';
        }
        if ($studentType === 'Bursary') {
            return 'Bursary Students';
        }
        if (in_array($studentType, ['Cash / Direct', 'Cash'], true)) {
            return 'Cash Students';
        }

        return null;
    }

    private function localFeeApplies(RoomTypes $roomType, ?string $studentRule): bool
    {
        if ($studentRule === 'NSFAS Students') {
            return $roomType->apply_nsfas !== null;
        }
        if ($studentRule === 'Bursary Students') {
            return $roomType->apply_bursary !== null;
        }
        if ($studentRule === 'Cash Students') {
            return $roomType->apply_cash !== null;
        }

        return false;
    }

    private function numberOrNull($value): ?float
    {
        return $value === null || $value === '' || !is_numeric($value) ? null : (float) $value;
    }
}
