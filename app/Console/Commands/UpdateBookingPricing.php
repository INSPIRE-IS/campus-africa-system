<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Lookups;
use App\Models\Booking;

class UpdateBookingPricing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:update_booking_price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $allBookings = Booking::where('year', $this_year)->where('userId', 9256)->get();
        $allBookings = Booking::where('year', $this_year)->where('id', '>', 8782)->get();

        foreach ($allBookings as $booking) {
            app('App\Http\Controllers\HomeController')->paymentDeatilsInternal($booking->userId, true);
            echo "Booking id " . $booking->id . " updated \n";
        }

        echo "Done";
    }
}
