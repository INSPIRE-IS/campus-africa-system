<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\RoomTypes;
use App\Models\Booking;

class FixPaymentData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:fix_payment_data';
    // php artisan command:fix_payment_data

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $allNsfasRooms = RoomTypes::where('use_nsfas', 1)->get();
        $nsfas_room_ids = [];

        foreach ($allNsfasRooms as $room) {
            array_push($nsfas_room_ids, $room->id);
        }

        $allBookings = Booking::whereIn('roomType', $nsfas_room_ids)->get();

        foreach ($allBookings as $booking) {
            app('App\Http\Controllers\HomeController')->paymentDeatilsInternal($booking->userId);
            echo $booking->id . " Booking done | ";
        }

        echo " -- All Done";
    }
}
