<?php

namespace App\Console\Commands;

use App\Jobs\SendNewBookingMail;
use App\Models\StudentsInfo;
use App\Models\User;
use App\Models\Lookups;
use Illuminate\Console\Command;
use App\Traits\EmailTrait;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use Illuminate\Support\Facades\Log;

class SendTestEmailCommand extends Command
{
    use EmailTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-test {email}';
    // php artisan email:send-test longjsjs@gmail.com

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email using EmailTrait';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $result = $this->sendTestEmail($email);
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        $this->info($result);

        $email = $this->argument('email');
        $result = $this->sendTestEmailAttachement($email);
        $this->info($result);


        $user = User::where('id', 17763)->first();
        $userInfo = StudentsInfo::where('userUid', 17763)->where('year', $this_year)->first();
        $generator = new LoginUrl($user);

        $generator->setRedirectUrl('/verify-email-account');
        $url = $generator->generate();

        dispatch(new SendNewBookingMail($user, $url, $this_year));


        // Log details for debugging
        $expiresAt = now()->addMinutes($user->login_route_expires_in);

        Log::info('Magic Login URL Generated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'magic_login_url' => $url,
            'expires_at' => $expiresAt->toDateTimeString(),
            'expires_timestamp' => $expiresAt->timestamp,
            'current_time' => now()->toDateTimeString(),
            'expires_in_human' => $expiresAt->diffForHumans(),
        ]);
    }
}
