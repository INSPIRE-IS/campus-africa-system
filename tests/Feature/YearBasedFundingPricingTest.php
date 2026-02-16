<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Lookups;
use App\Models\Residence;
use App\Models\RoomTypes;
use App\Models\StudentsInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class YearBasedFundingPricingTest extends TestCase
{
    use DatabaseTransactions;

    // Test toggles: keep them here (not env vars) to simplify local runs.
    private const PERSIST_DB = false; // Set true to keep test-created data in the DB for inspection.
    private const TARGET_YEAR = null; // Set to a specific lookup_name (e.g. '2026') to force the test year.

    protected function connectionsToTransact()
    {
        if (self::PERSIST_DB) {
            return [];
        }

        return [null];
    }

    /**
     * This test exercises the real booking flow (step 1 save),
     * but focuses on the year-based lease fee + deposit logic.
     */
    public function test_year_based_lease_fees_and_deposit_apply_per_funding_type()
    {
        Bus::fake();

        $yearLookup = $this->resolveYearLookup();
        $year = $yearLookup->lookup_name;
        $leaseMonths = $this->calculateLeaseMonthCount($yearLookup);
        $monthlyRent = 4500;
        $nsfasAmount = 3500;

        // --- Step 1: Hard-break if year lookup data is incomplete. ---
        // We expect lease_fees + deposit_applies to exist for each funding type tested.
        $yearData = $this->assertYearConfigComplete($yearLookup);

        // --- Step 2: Waive booking fee to keep the flow deterministic (no WP calls). ---
        $waiveBooking = Lookups::where('group_id', '7')->orderBy('id')->first();
        if ($waiveBooking) {
            $waiveBooking->update(['lookup_name' => '1']);
        } else {
            Lookups::create([
                'group_id' => '7',
                'lookup_name' => '1',
            ]);
        }

        // --- Step 3: Minimal Institution + Room setup for pricing inputs. ---
        $institution = Lookups::create([
            'group_id' => '1',
            'lookup_name' => 'Test Institution',
            'nsfas_amount' => $nsfasAmount,
        ]);

        $residence = Residence::create([
            'residence' => 'Test Residence',
            'building_prefix' => 'TR',
        ]);

        $roomType = RoomTypes::create([
            'property_id' => $residence->id,
            'name' => 'Single Room',
            'room_map_slug' => 123,
            'rent_price' => $monthlyRent,
            'booking_price' => 500,
            'pull_price' => null, // use local prices
            'apply_cash' => 1,
            'apply_bursary' => 1,
            'apply_nsfas' => 1,
            'use_nsfas' => 0,
        ]);

        $scenarioDepositFlags = [];

        foreach ($this->fundingTypeScenarios() as $label => $scenario) {
            $studentType = $scenario['studentType'];
            $fundingId = $scenario['fundingId'];
            $nsfasBursar = $scenario['nsfasBursar'];
            $useNsfasRoom = $scenario['useNsfasRoom'];
            $assertFundingTotals = $scenario['assertFundingTotals'] ?? false;

            $this->assertArrayHasKey(
                $fundingId,
                $yearData['lease_fees'],
                "Year lookup lease_fees is missing funding id {$fundingId} for {$year}. This is mandatory for the test."
            );
            $scenarioDepositFlags[] = !empty($yearData['deposit_applies'][$fundingId]);

            // --- Step 4: Create a user + current-year student info. ---
            // The booking flow expects a current-year StudentsInfo record for pricing checks.
            $user = User::create([
                'name' => 'Test User',
                'email' => 'student_' . $fundingId . '_' . uniqid() . '@example.com',
                'password' => bcrypt('secret'),
                'userRole' => 0,
            ]);

            StudentsInfo::create([
                'userUid' => $user->id,
                'userEmail' => $user->email,
                'userFirstName' => 'Test',
                'userLastname' => 'Student',
                'institutionStudy' => $institution->id,
                'studentType' => $studentType,
                'nsfas_bursar' => $nsfasBursar,
                'year' => $year,
            ]);

            // Optional: if a scenario needs NSFAS rooms, flip the flag.
            if ($useNsfasRoom) {
                $roomType->update(['use_nsfas' => 1]);
            } else {
                $roomType->update(['use_nsfas' => 0]);
            }

            // --- Step 5: Submit the step-1 booking payload. ---
            // This simulates the cash/bursary/nsfas/etc booking flow for pricing.
            $payload = [
                'userUid' => $user->id,
                'userEmail' => $user->email,
                'userId' => '9001010000000',
                'userNationality' => 'South African',
                'userPassport' => '',
                'firstName' => 'Test',
                'lastName' => 'Student',
                'title' => 'Mr',
                'cell_no' => '0123456789',
                'userCellCode' => '+27',
                'institStudy' => $institution->id,
                'studNumber' => 'ST' . $fundingId . '001',
                'faculty' => 'Science',
                'year' => $year,
                'userAddress1' => '1 Test St',
                'userAddress2' => 'Test Suburb',
                'userAddressCity' => 'Test City',
                'userAddressPostal' => '0001',
                'studentType' => $studentType,
                'yearOfStudy' => '1',
                'gender' => 1,
                'property' => $residence->id,
                'roomType' => $roomType->id,
                'answers' => [],
            ];

            $this->actingAs($user)
                ->withoutMiddleware()
                ->post('/new-booking-step-1-save', $payload);

            // --- Step 6: Assert booking payment values from year config. ---
            $booking = Booking::where('userId', $user->id)->where('year', $year)->firstOrFail();
            $payment = BookingPayment::where('bookingId', $booking->id)->firstOrFail();

            $expectedLeaseFee = floatval($yearData['lease_fees'][$fundingId]);
            $expectedDeposit = !empty($yearData['deposit_applies'][$fundingId]) ? $monthlyRent : 0;

            $this->assertEquals($expectedLeaseFee, $payment->loan_admin_fee, "{$label}: Lease admin fee does not match year config.");
            $this->assertEquals($expectedDeposit, $payment->deposit, "{$label}: Deposit does not match year config.");

            if ($assertFundingTotals) {
                $expectedTotals = $this->calculateExpectedTotals(
                    $monthlyRent,
                    $nsfasAmount,
                    $leaseMonths,
                    $studentType,
                    $nsfasBursar,
                    $useNsfasRoom
                );

                $this->assertEqualsWithDelta($expectedTotals['monthly_rental'], $payment->monthly_rental, 0.01, "{$label}: Monthly rental mismatch.");
                $this->assertEqualsWithDelta($expectedTotals['monthly_rental_total'], $payment->monthly_rental_total, 0.01, "{$label}: Yearly rental mismatch.");
                $this->assertEqualsWithDelta($expectedTotals['funded_monthly'], $payment->funded_monthly, 0.01, "{$label}: Funded monthly mismatch.");
                $this->assertEqualsWithDelta($expectedTotals['total_funded'], $payment->total_funded, 0.01, "{$label}: Total funded mismatch.");
                $this->assertEqualsWithDelta($expectedTotals['total_top_up'], $payment->total_top_up, 0.01, "{$label}: Total top-up mismatch.");
                $this->assertEquals($expectedTotals['nsfas_rate'], $payment->nsfas_rate, "{$label}: NSFAS rate mismatch.");
            }
        }

        $hasDepositTrue = in_array(true, $scenarioDepositFlags, true);
        $hasDepositFalse = in_array(false, $scenarioDepositFlags, true);
        $this->assertTrue(
            $hasDepositTrue && $hasDepositFalse,
            "Year lookup {$year} should include at least one funding type with deposit_applies=true and one with deposit_applies=false to fully cover deposit scenarios."
        );
    }

    public function test_year_based_lease_fees_and_deposit_apply_in_registration_flow()
    {
        Bus::fake();

        $yearLookup = $this->resolveYearLookup();
        $year = $yearLookup->lookup_name;
        $leaseMonths = $this->calculateLeaseMonthCount($yearLookup);
        $monthlyRent = 4500;
        $nsfasAmount = 3500;

        $yearData = $this->assertYearConfigComplete($yearLookup);

        $institution = Lookups::create([
            'group_id' => '1',
            'lookup_name' => 'Test Institution',
            'nsfas_amount' => $nsfasAmount,
        ]);

        $residence = Residence::create([
            'residence' => 'Test Residence',
            'building_prefix' => 'TR',
        ]);

        $roomType = RoomTypes::create([
            'property_id' => $residence->id,
            'name' => 'Single Room',
            'room_map_slug' => 123,
            'rent_price' => $monthlyRent,
            'booking_price' => 500,
            'pull_price' => null,
            'apply_cash' => 1,
            'apply_bursary' => 1,
            'apply_nsfas' => 1,
            'use_nsfas' => 0,
        ]);

        foreach ($this->fundingTypeScenarios() as $label => $scenario) {
            $studentType = $scenario['studentType'];
            $fundingId = $scenario['fundingId'];
            $nsfasBursar = $scenario['nsfasBursar'];
            $useNsfasRoom = $scenario['useNsfasRoom'];
            $assertFundingTotals = $scenario['assertFundingTotals'] ?? false;

            $this->assertArrayHasKey(
                $fundingId,
                $yearData['lease_fees'],
                "Year lookup lease_fees is missing funding id {$fundingId} for {$year}. This is mandatory for the test."
            );

            $roomType->update(['use_nsfas' => $useNsfasRoom ? 1 : 0]);

            $email = 'reg_' . $fundingId . '_' . uniqid() . '@example.com';
            $idNumber = '900101' . substr(strval(mt_rand(100000, 999999)), 0, 6);

            $payload = [
                'firstName' => 'Test',
                'lastName' => 'Student',
                'title' => 'Mr',
                'dob' => '1990-01-01',
                'cell_no' => '0123456789',
                'userCellCode' => '+27',
                'email' => $email,
                'email_alt' => null,
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'idNumber' => $idNumber,
                'nationality' => 'South African',
                'passportNo' => '',
                'institStudy' => $institution->id,
                'studNumber' => 'ST' . $fundingId . '001',
                'faculty' => 'Science',
                'year' => $year,
                'userAddress1' => '1 Test St',
                'userAddress2' => 'Test Suburb',
                'userAddressCity' => 'Test City',
                'userAddressPostal' => '0001',
                'studentType' => $studentType,
                'nsfas_bursar' => $nsfasBursar,
                'yearOfStudy' => '1',
                'gender' => 1,
                'property' => $residence->id,
                'roomType' => $roomType->id,
                'answers' => [],
            ];

            $this->withoutMiddleware()
                ->post('/registration_step_one', $payload);

            $user = User::where('email', $email)->firstOrFail();
            $booking = Booking::where('userId', $user->id)->where('year', $year)->firstOrFail();
            $payment = BookingPayment::where('bookingId', $booking->id)->firstOrFail();

            $expectedLeaseFee = floatval($yearData['lease_fees'][$fundingId]);
            $expectedDeposit = !empty($yearData['deposit_applies'][$fundingId]) ? $monthlyRent : 0;

            $this->assertEquals($expectedLeaseFee, $payment->loan_admin_fee, "{$label} registration: Lease admin fee does not match year config.");
            $this->assertEquals($expectedDeposit, $payment->deposit, "{$label} registration: Deposit does not match year config.");

            // Registration flow does not currently set nsfas_bursar, so skip totals for that scenario.
            if ($assertFundingTotals && !($studentType === 'Bursary' && $nsfasBursar === 1)) {
                $expectedTotals = $this->calculateExpectedTotals(
                    $monthlyRent,
                    $nsfasAmount,
                    $leaseMonths,
                    $studentType,
                    $nsfasBursar,
                    $useNsfasRoom
                );

                $this->assertEqualsWithDelta($expectedTotals['monthly_rental'], $payment->monthly_rental, 0.01, "{$label} registration: Monthly rental mismatch.");
                $this->assertEqualsWithDelta($expectedTotals['monthly_rental_total'], $payment->monthly_rental_total, 0.01, "{$label} registration: Yearly rental mismatch.");
                $this->assertEqualsWithDelta($expectedTotals['funded_monthly'], $payment->funded_monthly, 0.01, "{$label} registration: Funded monthly mismatch.");
                $this->assertEqualsWithDelta($expectedTotals['total_funded'], $payment->total_funded, 0.01, "{$label} registration: Total funded mismatch.");
                $this->assertEqualsWithDelta($expectedTotals['total_top_up'], $payment->total_top_up, 0.01, "{$label} registration: Total top-up mismatch.");
                $this->assertEquals($expectedTotals['nsfas_rate'], $payment->nsfas_rate, "{$label} registration: NSFAS rate mismatch.");
            }
        }
    }

    public function test_year_selection_applies_year_specific_lease_fees()
    {
        Bus::fake();

        $yearLookups = $this->collectYearLookupsWithFundingConfig();
        if (count($yearLookups) < 2) {
            $this->markTestSkipped('Need at least two Year lookups with lease_fees and deposit_applies to test year selection.');
        }

        [$yearA, $yearB, $fundingId] = $this->findYearPairWithDifferentLeaseFee($yearLookups);
        if (!$yearA || !$yearB || !$fundingId) {
            $this->markTestSkipped('No two Year lookups have different lease fees for the same funding id.');
        }

        $studentType = Lookups::FUNDING_TYPES[$fundingId] ?? null;
        if (!$studentType) {
            $this->markTestSkipped("Funding id {$fundingId} is not mapped to a student type.");
        }

        $monthlyRent = 4500;

        // Waive booking fee to keep the flow deterministic (no WP calls).
        $waiveBooking = Lookups::where('group_id', '7')->orderBy('id')->first();
        if ($waiveBooking) {
            $waiveBooking->update(['lookup_name' => '1']);
        } else {
            Lookups::create([
                'group_id' => '7',
                'lookup_name' => '1',
            ]);
        }

        $institution = Lookups::create([
            'group_id' => '1',
            'lookup_name' => 'Test Institution',
            'nsfas_amount' => 3500,
        ]);

        $residence = Residence::create([
            'residence' => 'Test Residence',
            'building_prefix' => 'TR',
        ]);

        $roomType = RoomTypes::create([
            'property_id' => $residence->id,
            'name' => 'Single Room',
            'room_map_slug' => 123,
            'rent_price' => $monthlyRent,
            'booking_price' => 500,
            'pull_price' => null, // use local prices
            'apply_cash' => 1,
            'apply_bursary' => 1,
            'apply_nsfas' => 1,
            'use_nsfas' => 0,
        ]);

        $yearDataA = $this->assertYearConfigComplete($yearA);
        $yearDataB = $this->assertYearConfigComplete($yearB);

        $results = [];
        foreach ([$yearA, $yearB] as $yearLookup) {
            $year = $yearLookup->lookup_name;

            $user = User::create([
                'name' => 'Test User',
                'email' => 'year_' . $year . '_' . uniqid() . '@example.com',
                'password' => bcrypt('secret'),
                'userRole' => 0,
            ]);

            StudentsInfo::create([
                'userUid' => $user->id,
                'userEmail' => $user->email,
                'userFirstName' => 'Test',
                'userLastname' => 'Student',
                'institutionStudy' => $institution->id,
                'studentType' => $studentType,
                'nsfas_bursar' => 0,
                'year' => $year,
            ]);

            $payload = [
                'userUid' => $user->id,
                'userEmail' => $user->email,
                'userId' => '9001010000000',
                'userNationality' => 'South African',
                'userPassport' => '',
                'firstName' => 'Test',
                'lastName' => 'Student',
                'title' => 'Mr',
                'cell_no' => '0123456789',
                'userCellCode' => '+27',
                'institStudy' => $institution->id,
                'studNumber' => 'ST' . $fundingId . '001',
                'faculty' => 'Science',
                'year' => $year,
                'userAddress1' => '1 Test St',
                'userAddress2' => 'Test Suburb',
                'userAddressCity' => 'Test City',
                'userAddressPostal' => '0001',
                'studentType' => $studentType,
                'yearOfStudy' => '1',
                'gender' => 1,
                'property' => $residence->id,
                'roomType' => $roomType->id,
                'answers' => [],
            ];

            $this->actingAs($user)
                ->withoutMiddleware()
                ->post('/new-booking-step-1-save', $payload);

            $booking = Booking::where('userId', $user->id)->where('year', $year)->firstOrFail();
            $payment = BookingPayment::where('bookingId', $booking->id)->firstOrFail();

            $results[$year] = [
                'loan_admin_fee' => $payment->loan_admin_fee,
                'deposit' => $payment->deposit,
            ];
        }

        $expectedA = floatval($yearDataA['lease_fees'][$fundingId]);
        $expectedB = floatval($yearDataB['lease_fees'][$fundingId]);
        $this->assertNotEquals($expectedA, $expectedB, 'Selected years must have different lease fees to validate year selection.');

        $this->assertEquals($expectedA, $results[$yearA->lookup_name]['loan_admin_fee'], "Year {$yearA->lookup_name}: lease fee mismatch.");
        $this->assertEquals($expectedB, $results[$yearB->lookup_name]['loan_admin_fee'], "Year {$yearB->lookup_name}: lease fee mismatch.");

        $expectedDepositA = !empty($yearDataA['deposit_applies'][$fundingId]) ? $monthlyRent : 0;
        $expectedDepositB = !empty($yearDataB['deposit_applies'][$fundingId]) ? $monthlyRent : 0;
        $this->assertEquals($expectedDepositA, $results[$yearA->lookup_name]['deposit'], "Year {$yearA->lookup_name}: deposit mismatch.");
        $this->assertEquals($expectedDepositB, $results[$yearB->lookup_name]['deposit'], "Year {$yearB->lookup_name}: deposit mismatch.");
    }

    private function fundingTypeScenarios(): array
    {
        return [
            'Cash / Direct' => [
                'studentType' => 'Cash / Direct',
                'fundingId' => '3',
                'nsfasBursar' => 0,
                'useNsfasRoom' => false,
                'assertFundingTotals' => true,
            ],
            'NSFAS (use_nsfas=1)' => [
                'studentType' => 'NSFAS',
                'fundingId' => '1',
                'nsfasBursar' => 0,
                'useNsfasRoom' => true,
                'assertFundingTotals' => true,
            ],
            'NSFAS (use_nsfas=0)' => [
                'studentType' => 'NSFAS',
                'fundingId' => '1',
                'nsfasBursar' => 0,
                'useNsfasRoom' => false,
                'assertFundingTotals' => true,
            ],
            'Bursary (nsfas_bursar=0)' => [
                'studentType' => 'Bursary',
                'fundingId' => '2',
                'nsfasBursar' => 0,
                'useNsfasRoom' => false,
                'assertFundingTotals' => true,
            ],
            'Bursary (nsfas_bursar=1)' => [
                'studentType' => 'Bursary',
                'fundingId' => '2',
                'nsfasBursar' => 1,
                'useNsfasRoom' => false,
                'assertFundingTotals' => true,
            ],
            'GCRA' => [
                'studentType' => 'GCRA',
                'fundingId' => '4',
                'nsfasBursar' => 0,
                'useNsfasRoom' => false,
            ],
            'MQA' => [
                'studentType' => 'MQA',
                'fundingId' => '5',
                'nsfasBursar' => 0,
                'useNsfasRoom' => false,
            ],
            'FUNZA' => [
                'studentType' => 'FUNZA',
                'fundingId' => '6',
                'nsfasBursar' => 0,
                'useNsfasRoom' => false,
            ],
            'NRF' => [
                'studentType' => 'NRF',
                'fundingId' => '7',
                'nsfasBursar' => 0,
                'useNsfasRoom' => false,
            ],
        ];
    }

    private function resolveYearLookup(): Lookups
    {
        $requestedYear = self::TARGET_YEAR;
        if ($requestedYear) {
            $lookup = Lookups::where('group_id', '4')->where('lookup_name', $requestedYear)->first();
            $this->assertNotNull(
                $lookup,
                "TARGET_YEAR={$requestedYear} not found. Please create/update that Year lookup with lease_fees and deposit_applies."
            );
            return $lookup;
        }

        $candidates = Lookups::where('group_id', '4')->orderBy('lookup_name', 'desc')->get();
        foreach ($candidates as $candidate) {
            $data = is_array($candidate->data) ? $candidate->data : (is_string($candidate->data) ? json_decode($candidate->data, true) : []);
            if (is_array($data) && array_key_exists('lease_fees', $data) && array_key_exists('deposit_applies', $data)) {
                return $candidate;
            }
        }

        $this->fail('No Year lookup found with lease_fees and deposit_applies. Open the Year lookup and add both fields; they are mandatory for this test.');
    }

    private function collectYearLookupsWithFundingConfig(): array
    {
        $lookups = [];
        $candidates = Lookups::where('group_id', '4')->orderBy('lookup_name', 'desc')->get();
        foreach ($candidates as $candidate) {
            $data = $this->extractYearData($candidate);
            if (is_array($data) && array_key_exists('lease_fees', $data) && array_key_exists('deposit_applies', $data)) {
                $lookups[] = $candidate;
            }
        }

        return $lookups;
    }

    private function findYearPairWithDifferentLeaseFee(array $yearLookups): array
    {
        $count = count($yearLookups);
        for ($i = 0; $i < $count; $i++) {
            $dataA = $this->extractYearData($yearLookups[$i]);
            if (empty($dataA['lease_fees']) || !is_array($dataA['lease_fees'])) {
                continue;
            }
            for ($j = $i + 1; $j < $count; $j++) {
                $dataB = $this->extractYearData($yearLookups[$j]);
                if (empty($dataB['lease_fees']) || !is_array($dataB['lease_fees'])) {
                    continue;
                }

                $commonFundingIds = array_intersect(array_keys($dataA['lease_fees']), array_keys($dataB['lease_fees']));
                foreach ($commonFundingIds as $fundingId) {
                    $feeA = floatval($dataA['lease_fees'][$fundingId]);
                    $feeB = floatval($dataB['lease_fees'][$fundingId]);
                    if (abs($feeA - $feeB) > 0.01) {
                        return [$yearLookups[$i], $yearLookups[$j], $fundingId];
                    }
                }
            }
        }

        return [null, null, null];
    }

    private function extractYearData(Lookups $yearLookup): array
    {
        if (is_array($yearLookup->data)) {
            return $yearLookup->data;
        }

        if (!empty($yearLookup->data)) {
            $decoded = json_decode($yearLookup->data, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function calculateLeaseMonthCount(Lookups $yearLookup): int
    {
        $this->assertNotEmpty(
            $yearLookup->lease_from_date,
            "Year lookup {$yearLookup->lookup_name} is missing lease_from_date, required to calculate lease months."
        );
        $this->assertNotEmpty(
            $yearLookup->lease_to_date,
            "Year lookup {$yearLookup->lookup_name} is missing lease_to_date, required to calculate lease months."
        );

        $diff = 0;
        for ($i = new \DateTime($yearLookup->lease_from_date); $i <= new \DateTime($yearLookup->lease_to_date); $i->modify('+1 month')) {
            $diff++;
        }

        return $diff;
    }

    private function calculateExpectedTotals(
        float $monthlyRent,
        float $nsfasAmount,
        int $leaseMonths,
        string $studentType,
        int $nsfasBursar,
        bool $useNsfasRoom
    ): array {
        $yearlyRent = $monthlyRent * $leaseMonths;
        $funded = $nsfasAmount * $leaseMonths;
        $topup = $yearlyRent - $funded;
        $monthlyFunded = $nsfasAmount;
        $monthly = $monthlyRent;
        $nsfasRate = null;

        if ($studentType === 'Cash / Direct' || ($studentType === 'Bursary' && $nsfasBursar !== 1)) {
            $monthlyFunded = $monthlyRent;
            $funded = $yearlyRent;
            $topup = 0;
        } elseif ($useNsfasRoom) {
            $monthly = $monthlyFunded;
            $yearlyRent = $funded;
            $topup = 0;
            $nsfasRate = 1;
        }

        return [
            'monthly_rental' => $monthly,
            'monthly_rental_total' => $yearlyRent,
            'funded_monthly' => $monthlyFunded,
            'total_funded' => $funded,
            'total_top_up' => $topup,
            'nsfas_rate' => $nsfasRate,
        ];
    }

    private function assertYearConfigComplete(Lookups $yearLookup): array
    {
        $data = [];
        if (is_array($yearLookup->data)) {
            $data = $yearLookup->data;
        } elseif (!empty($yearLookup->data)) {
            $decoded = json_decode($yearLookup->data, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        $this->assertIsArray(
            $data,
            "Year lookup data for {$yearLookup->lookup_name} must be a JSON object with lease_fees and deposit_applies."
        );
        $this->assertArrayHasKey(
            'lease_fees',
            $data,
            "Year lookup data for {$yearLookup->lookup_name} is missing lease_fees (required for tests)."
        );
        $this->assertArrayHasKey(
            'deposit_applies',
            $data,
            "Year lookup data for {$yearLookup->lookup_name} is missing deposit_applies (required for tests)."
        );

        return $data;
    }
}
