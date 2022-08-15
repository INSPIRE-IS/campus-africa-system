<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingPayment extends Model
{
    use HasFactory;

    protected $table = 'booking_payments';

    protected $fillable = [
        'bookingId',
        'booking_fee',
        'booking_fee_waived',
        'rob_b_waived',
        'loan_admin_fee',
        'loan_admin_fee_waived',
        'rob_loan_waived',
        'deposit',
        'deposit_waived',
        'rob_deposit_waived',
        'monthly_rental',
        'monthly_rental_total',
        'total_funded',
        'total_top_up',
        'reason_booking_fee',
        'reason_admin_fee',
        'reason_deposit',
        'reason_booking_fee_reject',
        'reason_admin_fee_reject',
        'reason_deposit_reject',
        'funded_monthly'
    ];

    public function booking(){
        return $this->hasOne("App\Models\Booking", "id", "bookingId");
    }
}
