<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ApplicationToken;
use App\Jobs\SendApplicationThankYouMail;
use Grosv\LaravelPasswordlessLogin\LoginUrl; // Ensure this class exists

class TestApplicationMailCommand extends Command
{
    protected $signature = 'test:application-mail {userId}';
    protected $description = 'Test sending the application thank-you email';
    // php artisan test:application-mail 1

    public function handle()
    {
        $userId = $this->argument('userId');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return;
        }

        // Create a test application token
        $userToken = ApplicationToken::create([
            "userUid" => $user->id,
            "applicationToken" => $this->generateRandomLongString(),
            "verified" => 0,
            "security_token" => $this->generateRandomShortString()
        ]);

        // Generate Login URL
        $generator = new LoginUrl($user);
        $generator->setRedirectUrl('/verify-password');
        $url = $generator->generate();
        //$this->info("generator {$generator}");
        //$this->info("url {$url} .");
        //$this->info("User {$user} .");
        $this->info("sending");

        // Dispatch the email job
        dispatch(new SendApplicationThankYouMail($user, $userToken->security_token, $url, '2026'));

        $this->info("Test email dispatched successfully for User ID: {$userId}");
    }

    private function generateRandomLongString()
    {
        return bin2hex(random_bytes(32)); // Generates a 64-character random string
    }

    private function generateRandomShortString()
    {
        return bin2hex(random_bytes(8)); // Generates a 16-character random string
    }
}
