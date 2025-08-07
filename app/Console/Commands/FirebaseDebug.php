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
                            {--list-maintenance : Show maintenance items}';

    // # 🔎 View Firestore structure
    // php artisan firebase:debug --structure

    // # 👤 Look up a Firebase Auth user
    // php artisan firebase:debug --auth-user=example@domain.com
    // php artisan firebase:debug --auth-user=lincoln@fixrbuddy.co.za

    // # 👤 Look up a Firebase Auth user
    // php artisan firebase:debug --user=asdkjfasfhaskjhlasdhf
    // php artisan firebase:debug --user=lYyDg5XLAo1TD1uUxdIcVn3qyH5t1

    // # 🛠️ View latest maintenance items
    // php artisan firebase:debug --list-maintenance


    protected $description = 'Debug Firebase service connectivity, auth, and Firestore structure';

    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        parent::__construct();
        $this->firebase = $firebase;
    }

    public function handle()
    {
        if ($this->option('structure')) {
            $this->info('Fetching Firestore structure...');
            $structure = $this->firebase->getFirestoreStructure();
            dump($structure);
            return;
        }

        if ($emailOrUid = $this->option('auth-user')) {
            $this->info("Looking up user: {$emailOrUid}");
            try {
                /** @disregard P1014 */
                $user = filter_var($emailOrUid, FILTER_VALIDATE_EMAIL)
                    ? $this->firebase->getUserByEmail($emailOrUid)
                    : $this->firebase->getUser($emailOrUid);

                dump($user->uid, $user->email, $user->displayName);
            } catch (\Throwable $e) {
                $this->error("User not found or error: " . $e->getMessage());
            }
            return;
        }

        if ($emailOrUid = $this->option('user')) {
            $this->info("Looking up user: {$emailOrUid}");
            try {
                /** @disregard P1014 */
                $user = $this->firebase->getUserByEmailOrUid($emailOrUid);

                $this->info("✅ Firebase Auth User:");
                dump($user);
                dump($user->uid, $user->email, $user->displayName);

                // 🔍 Now fetch user profile from Firestore
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


        if ($this->option('list-maintenance')) {
            $this->info('Listing maintenance items...');
            $items = $this->firebase->getMaintenanceItems(10);
            foreach ($items as $item) {
                $this->line("ID: {$item->id()} | Data: " . json_encode($item->data()));
            }
            return;
        }

        $this->warn('No options provided. Use --structure, --user=EMAIL|UID or --list-maintenance');
    }
}
