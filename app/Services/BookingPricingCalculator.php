<?php

namespace App\Services;

use App\Models\Lookups;
use App\Models\RoomTypes;
use App\Models\Residence;
use DateTime;

class BookingPricingCalculator
{
    public static function fetchWpData()
    {
        $residences = json_decode(@file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
        $rooms = json_decode(@file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));

        if (!is_array($residences)) {
            $residences = [];
        }
        if (!is_array($rooms)) {
            $rooms = [];
        }

        return [$residences, $rooms];
    }

    public static function calculateWpPricingForBooking($booking, $studentInfo, $lookupLeaseDates, $waiveBookingFeeLookup, $wpResidences, $wpRooms)
    {
        if (!$booking || !$studentInfo || !$lookupLeaseDates) {
            return null;
        }

        static $roomTypeCache = [];
        static $residenceCache = [];
        static $institutionCache = [];
        static $yearConfigCache = [];

        $roomTypeKey = $booking->roomType;
        if (!$roomTypeKey) {
            return null;
        }

        if (!array_key_exists($roomTypeKey, $roomTypeCache)) {
            $roomTypeCache[$roomTypeKey] = RoomTypes::where('id', $roomTypeKey)->first();
        }
        $roomTypeData = $roomTypeCache[$roomTypeKey];
        if (!$roomTypeData) {
            return null;
        }

        $roomTypeId = $roomTypeData->room_map_slug;
        $res = null;
        if (method_exists($booking, 'relationLoaded') && $booking->relationLoaded('residence')) {
            $res = $booking->residence;
        }
        if (!$res && $booking->property) {
            if (!array_key_exists($booking->property, $residenceCache)) {
                $residenceCache[$booking->property] = Residence::where('id', $booking->property)->first();
            }
            $res = $residenceCache[$booking->property];
        }
        if (!$res) {
            return null;
        }

        $room_rate = 0;
        $deposit = 0;

        $is_nsfas = false;
        if ($roomTypeData->use_nsfas == 1) {
            $is_nsfas = true;
        }

        if ($waiveBookingFeeLookup && $waiveBookingFeeLookup->lookup_name == "1") {
            $room_rate = "0";
        } else {
            $residenceId = "";

            foreach ($wpResidences as $residence) {
                if (isset($residence->title->rendered) && $residence->title->rendered == $res->residence) {
                    $room_rate = $residence->acf->booking_fee;
                    $residenceId = $residence->id;
                }
            }

            $roomType = "";
            $roomName = "";

            foreach ($wpRooms as $room) {
                if ($residenceId == $room->acf->residence) {
                    if ($room->id == $roomTypeId) {
                        $roomType = $booking->roomType;
                        $roomName = $room->slug;
                        $deposit = $room->acf->price_from;
                        $appliesTo = $room->acf->booking_fee_applies_to;

                        if ($room->acf->booking_fee_override !== "") {
                            $room_rate = $room->acf->booking_fee_override;
                        }

                        if ($room->slug == $roomName && $roomType == $booking->roomType) {
                            if ($room->acf->booking_fee_override !== "") {
                                $room_rate = $room->acf->booking_fee_override;
                            }

                            if ($studentInfo->studentType === "NSFAS") {
                                if (!in_array("NSFAS Students", $appliesTo)) {
                                    $room_rate = "0";
                                }
                            }

                            if ($studentInfo->studentType === "Bursary") {
                                if (!in_array("Bursary Students", $appliesTo)) {
                                    $room_rate = "0";
                                }
                            }

                            if ($studentInfo->studentType === "Cash / Direct") {
                                if (!in_array("Cash Students", $appliesTo)) {
                                    $room_rate = "0";
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($roomTypeData && $roomTypeData->pull_price == null) {
            $room_rate = $roomTypeData->booking_price;
            $deposit = $roomTypeData->rent_price;

            if ($studentInfo->studentType == "NSFAS") {
                if ($roomTypeData->apply_nsfas == null) {
                    $room_rate = "0";
                }
            }

            if ($studentInfo->studentType == "Bursary") {
                if ($roomTypeData->apply_bursary == null) {
                    $room_rate = "0";
                }
            }

            if ($studentInfo->studentType == "Cash / Direct") {
                if ($roomTypeData->apply_cash == null) {
                    $room_rate = "0";
                }
            }
        }

        $bookingPrice = $room_rate;

        $diff = 0;
        for ($i = new DateTime($lookupLeaseDates['lease_from_date']); $i <= new DateTime($lookupLeaseDates['lease_to_date']); $i->modify('+1 month')) {
            $diff++;
        }
        $monthlyRent = floatval($deposit);
        $yearly_rent = $monthlyRent * floatval($diff);

        $institutionKey = $studentInfo->institutionStudy;
        if ($institutionKey && !array_key_exists($institutionKey, $institutionCache)) {
            $institutionCache[$institutionKey] = Lookups::where('id', $institutionKey)->first();
        }
        $institution = $institutionKey ? ($institutionCache[$institutionKey] ?? null) : null;
        $funded = $institution ? floatval($institution->nsfas_amount) * floatval($diff) : 0;

        $topup = floatval($yearly_rent) - floatval($funded);
        $monthlyFunded = $institution ? $institution->nsfas_amount : 0;

        $nsfas_rate = null;

        $monthly = $monthlyRent;

        if (($studentInfo->studentType == "Cash / Direct") || ($studentInfo->studentType == "Bursary" && $studentInfo->nsfas_bursar !== 1)) {
            $monthlyFunded = $monthlyRent;
            $funded = $yearly_rent;
            $topup = '0';
        } else {
            if ($is_nsfas === true) {
                $monthly = $monthlyFunded;
                $yearly_rent = $funded;
                $topup = 0;
                $nsfas_rate = 1;
            }
        }

        $admin_fee = 0;
        $deposit = 0;
        $fundingId = Lookups::fundingTypeIdForLabel($studentInfo->studentType);
        $yearKey = $booking->year ?? $studentInfo->year ?? null;

        if ($fundingId && $yearKey) {
            if (!array_key_exists($yearKey, $yearConfigCache)) {
                $lookup = Lookups::where('group_id', '4')->where('lookup_name', $yearKey)->first();
                $data = [];
                if ($lookup) {
                    if (is_array($lookup->data)) {
                        $data = $lookup->data;
                    } elseif (!empty($lookup->data)) {
                        $decoded = json_decode($lookup->data, true);
                        if (is_array($decoded)) {
                            $data = $decoded;
                        }
                    }
                }

                $yearConfigCache[$yearKey] = [
                    'lease_fees' => is_array($data) ? ($data['lease_fees'] ?? []) : [],
                    'deposit_applies' => is_array($data) ? ($data['deposit_applies'] ?? []) : [],
                ];
            }

            $yearConfig = $yearConfigCache[$yearKey];
            $leaseFees = $yearConfig['lease_fees'] ?? [];
            $depositApplies = $yearConfig['deposit_applies'] ?? [];

            if (array_key_exists($fundingId, $leaseFees)) {
                $admin_fee = floatval($leaseFees[$fundingId]);
            }

            if (array_key_exists($fundingId, $depositApplies) && $depositApplies[$fundingId]) {
                $deposit = $monthlyRent;
            }
        }

        return [
            "booking_fee" => $bookingPrice,
            "booking_fee_waived" => 0,
            "rob_b_waived" => 0,
            "loan_admin_fee" => $admin_fee,
            "loan_admin_fee_waived" => 0,
            "rob_loan_waived" => 0,
            "deposit" => floatval($deposit),
            "deposit_waived" => 0,
            "rob_deposit_waived" => 0,
            "monthly_rental" => $monthly,
            "monthly_rental_total" => $yearly_rent,
            "total_funded" => $funded,
            "total_top_up" => $topup,
            "funded_monthly" => $monthlyFunded,
            "nsfas_rate" => $nsfas_rate,
        ];
    }
}
