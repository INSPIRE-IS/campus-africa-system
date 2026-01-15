<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class FirebaseDebug extends Command
{
    protected $signature = 'firebase:debug 
                            {--structure : Show Firestore structure} 
                            {--auth-user= : Lookup a user by email or UID}
                            {--user= : Lookup a user by email or UID}
                            {--password= : New password to update}
                            {--password-user= : User email or UID for password update}
                            {--email= : New email to update}
                            {--email-user= : User UID for email update}
                            {--list-maintenance : Show maintenance items}';

    protected $description = 'Debug Firebase service connectivity, auth, and Firestore structure';

    protected $firebase;

    // # 🔎 View Firestore structure
    // php artisan firebase:debug --structure
    // students@conurban.co.za Stud3nt@2025

    // # 👤 Look up a Firebase Auth user
    // php artisan firebase:debug --auth-user=example@domain.com
    // php artisan firebase:debug --auth-user=lincoln@fixrbuddy.co.za
    // php artisan firebase:debug --auth-user=jason@whitehart.co.za
    // php artisan firebase:debug --auth-user=students@conurban.co.za


    // # 👤 Look up a Firebase Auth user
    // php artisan firebase:debug --user=asdkjfasfhaskjhlasdhf
    // php artisan firebase:debug --user=lYyDg5XLAo1TD1uUxdIcVn3qyH5t1

    // # 👤 Update a Firebase Auth user password 
    // php artisan firebase:debug --password-user=someemail@domain.com --password=newStrongPassword
    // php artisan firebase:debug --password-user=someUID --password=newStrongPassword
    // php artisan firebase:debug --password-user=mKjbozRQJqR2dSmTXBbcCVJMMFE3 --password=tester1234

    // # 👤 Update a Firebase Auth user email 
    // php artisan firebase:debug --email-user=someUID --email=newemail@domain.com
    // php artisan firebase:debug --email-user=mKjbozRQJqR2dSmTXBbcCVJMMFE3 --email=longjsj@gmail.com
    // 

    // # 🛠️ View latest maintenance items
    // php artisan firebase:debug --list-maintenance


    public function __construct(FirebaseService $firebase)
    {
        parent::__construct();
        $this->firebase = $firebase;
    }

    public function handle()
    {
        // Firestore structure
        if ($this->option('structure')) {
            $this->info('Fetching Firestore structure...');
            $structure = $this->firebase->getFirestoreStructure();
            dump($structure);
            return;
        }

        // Lookup by auth-user
        if ($emailOrUid = $this->option('auth-user')) {
            $this->info("Looking up user: {$emailOrUid}");
            try {
                $user = filter_var($emailOrUid, FILTER_VALIDATE_EMAIL)
                    ? $this->firebase->getUserByEmail($emailOrUid)
                    : $this->firebase->getUserByUid($emailOrUid);

                dump($user->uid, $user->email, $user->displayName);
            } catch (\Throwable $e) {
                $this->error("User not found or error: " . $e->getMessage());
            }
            return;
        }

        // Lookup by user (combined)
        if ($emailOrUid = $this->option('user')) {
            $this->info("Looking up user: {$emailOrUid}");
            try {
                $user = $this->firebase->getUserByEmailOrUid($emailOrUid);

                $this->info("✅ Firebase Auth User:");
                dump($user);
                dump($user->uid, $user->email, $user->displayName);

                // Firestore profile
                $profile = $this->firebase->getUserProfileByUid($user->uid);

                if ($profile) {
                    $this->info("📄 Firestore user document:");
                    dump($profile);
                } else {
                    $this->warn("No user profile found in Firestore for UID: {$user->uid}");
                }
            } catch (\Throwable $e) {
                $this->error("User not found or error: " . $e->getMessage());
            }
            return;
        }

        // Update password: requires --password-user and --password
        if ($this->option('password-user') && $this->option('password')) {
            $identifier = $this->option('password-user');
            $newPassword = $this->option('password');

            $this->info("Updating password for user: {$identifier}");
            try {
                $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
                    ? $this->firebase->getUserByEmail($identifier)
                    : $this->firebase->getUserByUid($identifier);

                $this->firebase->updatePassword($user->uid, $newPassword);
                $this->info("🔑 Password updated.");
            } catch (\Throwable $e) {
                $this->error("Error updating password: " . $e->getMessage());
            }
            return;
        }

        // Update email: requires --email-user (uid) and --email (new email)
        if ($this->option('email-user') && $this->option('email')) {
            $uid = $this->option('email-user');
            $newEmail = $this->option('email');

            $this->info("Updating email for user UID: {$uid} to {$newEmail}");
            try {
                $this->firebase->updateEmail($uid, $newEmail);
                $this->info("📧 Email updated.");
            } catch (\Throwable $e) {
                $this->error("Error updating email: " . $e->getMessage());
            }
            return;
        }

        // List maintenance items
        if ($this->option('list-maintenance')) {
            $this->info('Listing maintenance items...');
            $items = $this->firebase->getMaintenanceItems(10, '');
            foreach ($items as $item) {
                $this->line("ID: {$item->id()} | Data: " . json_encode($item->data()));
            }
            return;
        }

        $this->warn('No options provided or missing required parameters. Use --help for usage.');
    }
}
