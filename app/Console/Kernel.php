<?php

namespace App\Console;

use App\Models\Lookups;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        foreach (Lookups::uiYearLookups() as $yearLookup) {
            $schedule->command('calculate:compatibility --year=' . $yearLookup->lookup_name)
                ->dailyAt('23:59');
        }

        $schedule->command('metrics:prune')
            ->dailyAt('01:10');
        $schedule->command('booking:send-monthly-pop')
            ->dailyAt('06:00');
        // $schedule->command('inspire')->hourly();
        // $schedule->call(function(){
        //     info('Called every minute');
        // })->dailyAt('15:30');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
