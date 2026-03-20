<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Services\MetricsCollector;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (app()->environment(['local'])) {
            // Log::info('Mail is set to sandbox');
            Mail::alwaysTo('sandbox@inspireonline.co.za'); // O@BczDAVgrz2{89e
        }
        if (app()->environment(['staging', 'development'])) {
            // Log::info('Mail is set to sandbox'); conurbandigital@gmail.com
            Mail::alwaysTo([
                'owethu@conurban.co.za',
                'sandbox@inspireonline.co.za'
            ]);
        }

        View::composer('*', function ($view) { // O@BczDAVgrz2{89e
            $agent = new Agent();
            $sessionYear = Session::get('year', now()->year);
            $bedCode = null;

            if (Auth::check()) {
                $userId = Auth::user()->id;
                $user = User::find($userId);

                //Log::info('checking bed code ', [$user]);
                if ($user && $user->userRole != 1) {
                    // Only check bed code if not userRole 1
                    $thisYear = now()->year;
                    /** @disregard P1013 */
                    $studentInfo = $user->studentInfoForYear($thisYear)->first();
                    //Log::info("Bed Code: " . $thisYear);
                    if ($studentInfo && $studentInfo->booking) {
                        //Log::info("Bed Code: ", [$studentInfo->booking]);
                        $bedCode = $studentInfo->booking->bed_code;
                    }
                    //Log::info("Bed Code: " . $bedCode);
                }
            }

            $view->with([
                'agent'       => $agent,
                'sessionYear' => $sessionYear,
                'bedCode'     => $bedCode
            ]);
        });

        DB::listen(function ($query) {
            app(MetricsCollector::class)->addQueryTime($query->time);
        });
    }
}
