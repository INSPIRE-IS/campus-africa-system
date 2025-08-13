<?php

// app/Console/Commands/TestPassword.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestPassword extends Command
{
    protected $signature = 'test:password {method} {email} {password}';
    protected $description = 'Test if a given password matches a user';
    // php artisan test:password check "nkosilusanda73@gmail.com" "betty123"
    // php artisan test:password change "nkosilusanda73@gmail.com" "bunny1234"
    public function handle()
    {
        $userId = 1;

        if ($this->argument('method') == 'check') {
            $user = User::where('email', $this->argument('email'))->first();

            if (!$user) {
                $this->error('User not found.');
                return;
            }

            if (Hash::check($this->argument('password'), $user->password)) {
                $this->info('✅ Password is correct.');
            } else {
                $this->error('❌ Password is incorrect.');

                $this->error(Hash::make($this->argument('password')));
                $this->error($user->password);
            }
        }

        if ($this->argument('method') == 'change') {
            $user = User::where('email', $this->argument('email'))->first();

            if (!$user) {
                $this->error('User not found.');
                return;
            }
            try {
                $this->info('✅ Password. ' . $user->password);
                $user->password = Hash::make($this->argument('password'));
                $user->save();
                $this->info('✅ Password updated. ' . $user->password);
                // User::where('id', $userId)->update(['password' => Hash::make($this->argument('password'))]);                
            } catch (\Throwable $e) {
                $this->error('❌ Error updating password: ' . $e->getMessage());
            }
        }
    }
}
