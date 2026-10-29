<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Lookups;
use App\Models\Residence;
use App\Models\RoomTypes;
use App\Models\StudentsInfo;
use App\Models\User;
use App\Services\PricingInspectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PricingInspectorController extends Controller
{
    public function index(Request $request, PricingInspectorService $inspector)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }
        if ((int) Auth::user()->role_super !== 1) {
            abort(403, 'Unauthorized');
        }

        $residences = Residence::whereNull('status')->orderBy('residence')->get();
        $years = Lookups::where('group_id', 4)->orderByDesc('lookup_name')->get();
        $globalWaiver = (string) optional(Lookups::where('group_id', 7)->first())->lookup_name === '1';
        $mode = $request->input('mode');
        $audit = null;
        $matrix = collect();
        $message = null;
        $wpData = null;

        if ($mode === 'booking') {
            $validated = $request->validate([
                'search' => 'required|string|max:255',
                'year' => 'nullable|integer',
            ]);
            $booking = $this->findBooking(trim($validated['search']), $validated['year'] ?? null);

            if (!$booking) {
                $message = 'No booking was found for that search and year.';
            } else {
                $student = StudentsInfo::where('userUid', $booking->userId)->where('year', $booking->year)->first();
                $residence = Residence::where('id', $booking->property)->first();
                $roomType = RoomTypes::where('id', $booking->roomType)->first();

                if (!$student || !$residence || !$roomType) {
                    $message = 'The booking is missing student, residence, or room-type data required for pricing.';
                } else {
                    $wpData = $inspector->fetchWordPressData();
                    $result = $inspector->inspect($residence, $roomType, (string) $student->studentType, $wpData, $globalWaiver);
                    $payment = BookingPayment::where('bookingId', $booking->id)->first();
                    $storedFee = $payment && $payment->booking_fee !== null ? (float) $payment->booking_fee : null;
                    $expectedFee = $result['expected_fee'];

                    $audit = [
                        'booking' => $booking,
                        'student' => $student,
                        'payment' => $payment,
                        'result' => $result,
                        'stored_fee' => $storedFee,
                        'booking_room_rate' => $booking->room_rate !== null ? (float) $booking->room_rate : null,
                        'matches' => $storedFee !== null && $expectedFee !== null
                            ? abs($storedFee - $expectedFee) < 0.01
                            : null,
                    ];
                }
            }
        }

        if ($mode === 'matrix') {
            $validated = $request->validate([
                'property_id' => 'required|integer|exists:property,id',
            ]);
            $residence = Residence::where('id', $validated['property_id'])->firstOrFail();
            $roomTypes = RoomTypes::where('property_id', $residence->id)->orderBy('name')->get();
            $wpData = $inspector->fetchWordPressData();

            foreach ($roomTypes as $roomType) {
                foreach (Lookups::FUNDING_TYPES as $studentType) {
                    $matrix->push($inspector->inspect($residence, $roomType, $studentType, $wpData, $globalWaiver));
                }
            }

            if ($roomTypes->isEmpty()) {
                $message = 'No local room types are configured for this residence.';
            }
        }

        return view('admin.metrics_pricing_inspector', [
            'headerTitle' => 'Pricing Inspector',
            'title' => 'Pricing Inspector',
            'residences' => $residences,
            'years' => $years,
            'fundingTypes' => Lookups::FUNDING_TYPES,
            'globalWaiver' => $globalWaiver,
            'mode' => $mode,
            'audit' => $audit,
            'matrix' => $matrix,
            'message' => $message,
            'wpData' => $wpData,
            'selectedPropertyId' => (int) $request->input('property_id'),
        ]);
    }

    private function findBooking(string $search, $year = null): ?Booking
    {
        $bookingQuery = Booking::query();
        if ($year) {
            $bookingQuery->where('year', (string) $year);
        }

        $booking = (clone $bookingQuery)->where('booking_code', $search)->first();
        if ($booking) {
            return $booking;
        }

        $userId = null;
        if (ctype_digit($search)) {
            $userId = (int) $search;
        } elseif (strpos($search, '@') !== false) {
            $userId = User::withTrashed()
                ->where('email', $search)
                ->orWhere('userEmail', $search)
                ->value('id');
        } else {
            $studentQuery = StudentsInfo::where('studentNo', $search);
            if ($year) {
                $studentQuery->where('year', (string) $year);
            }
            $userId = $studentQuery->orderByDesc('year')->value('userUid');
        }

        if (!$userId) {
            return null;
        }

        return $bookingQuery->where('userId', $userId)->orderByDesc('year')->first();
    }
}
