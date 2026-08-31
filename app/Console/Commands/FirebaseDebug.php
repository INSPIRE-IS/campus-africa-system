<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\FirebaseRegistrationLog;
use App\Models\Lookups;
use App\Models\Residence;
use App\Models\StudentsInfo;
use App\Models\User;
use App\Services\FirebaseService;

class FirebaseDebug extends Command
{
    protected $signature = 'firebase:debug 
                            {--structure : Show Firestore structure} 
                            {--auth-user= : Lookup a user by email or UID}
                            {--user= : Lookup a user by email or UID}
                            {--lookup-user= : Unified lookup across Auth + Firestore}
                            {--check-student= : Check Laravel current-year maintenance app eligibility plus Firebase status by email or user ID}
                            {--year= : Year for --check-student, defaults to current calendar year}
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
    // php artisan firebase:debug --auth-user=ishmaelalundoundou@gmail.com
    // php artisan firebase:debug --auth-user=nokwandadube32@gmail.com
    // php artisan firebase:debug --check-student=ishmaelalundoundou@gmail.com --year=2026
    // php artisan firebase:debug --check-student=nokwandadube32@gmail.com --year=2026


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

    // # 🛠️ View latest maintenance items
    // php artisan firebase:debug --lookup-user=example@domain.com
    // php artisan firebase:debug --lookup-user=UID
    // php artisan firebase:debug --lookup-user=students@conurban.co.za
    // php artisan firebase:debug --lookup-user=tlodipjanemalemela@gmail.com
    // php artisan firebase:debug --lookup-user=mKjbozRQJqR2dSmTXBbcCVJMMFE3

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Firestore structure
        if ($this->option('structure')) {
            $this->info('Fetching Firestore structure...');
            $structure = $this->firebase()->getFirestoreStructure();
            dump($structure);
            return;
        }

        if ($identifier = $this->option('check-student')) {
            $year = (string) ($this->option('year') ?: now()->format('Y'));
            $this->checkStudent($identifier, $year);
            return;
        }

        // Lookup by auth-user
        if ($emailOrUid = $this->option('auth-user')) {
            $this->info("Looking up user: {$emailOrUid}");
            try {
                $user = filter_var($emailOrUid, FILTER_VALIDATE_EMAIL)
                    ? $this->firebase()->getUserByEmail($emailOrUid)
                    : $this->firebase()->getUserByUid($emailOrUid);

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
                $user = $this->firebase()->getUserByEmailOrUid($emailOrUid);

                $this->info("✅ Firebase Auth User:");
                dump($user);
                dump($user->uid, $user->email, $user->displayName);

                // Firestore profile
                $profile = $this->firebase()->getUserProfileByUid($user->uid);

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
                    ? $this->firebase()->getUserByEmail($identifier)
                    : $this->firebase()->getUserByUid($identifier);

                $this->firebase()->updatePassword($user->uid, $newPassword);
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
                $this->firebase()->updateEmail($uid, $newEmail);
                $this->info("📧 Email updated.");
            } catch (\Throwable $e) {
                $this->error("Error updating email: " . $e->getMessage());
            }
            return;
        }

        // List maintenance items
        if ($this->option('list-maintenance')) {
            $this->info('Listing maintenance items...');
            $items = $this->firebase()->getMaintenanceItems(10, '');
            foreach ($items as $item) {
                $this->line("ID: {$item->id()} | Data: " . json_encode($item->data()));
            }
            return;
        }

        // Unified lookup for auth + firestore
        if ($emailOrUid = $this->option('lookup-user')) {
            $this->info("Looking up user across Firebase Auth + Firestore: {$emailOrUid}");

            try {
                // Try email first, fallback to UID
                $user = filter_var($emailOrUid, FILTER_VALIDATE_EMAIL)
                    ? $this->firebase()->getUserByEmail($emailOrUid)
                    : $this->firebase()->getUserByUid($emailOrUid);

                $this->info("🔍 Firebase Auth User:");
                dump([
                    'uid' => $user->uid,
                    'email' => $user->email,
                    'displayName' => $user->displayName,
                    'phoneNumber' => $user->phoneNumber,
                ]);

                // Get Firestore profile
                $profile = $this->firebase()->getUserProfileByUid($user->uid);

                if ($profile) {
                    $this->info("📄 Firestore User Document:");
                    dump($profile);
                } else {
                    $this->warn("⚠ No Firestore document found for UID: {$user->uid}");
                }

                // Optionally, show maintenance items assigned to user
                $this->info("🛠 Maintenance Items for this user:");
                $items = $this->firebase()->getMaintenanceItemsForUser($user->uid);

                if (!$items->isEmpty()) {
                    foreach ($items as $item) {
                        $this->line("• {$item->id()} — " . json_encode($item->data()));
                    }
                } else {
                    $this->line("No maintenance items linked to this user.");
                }
            } catch (\Throwable $e) {
                $this->error("Error: " . $e->getMessage());
            }

            return;
        }


        $this->warn('No options provided or missing required parameters. Use --help for usage.');
    }

    private function firebase(): FirebaseService
    {
        if (!$this->firebase) {
            $this->firebase = app(FirebaseService::class);
        }

        return $this->firebase;
    }

    private function checkStudent(string $identifier, string $year): void
    {
        $user = $this->findLocalUser($identifier);

        $this->info("Firebase maintenance app check");
        $this->line("Identifier: {$identifier}");
        $this->line("Year: {$year}");

        if (!$user) {
            $this->error('Local user: not found.');
            $this->showFirebaseStatus($identifier);
            return;
        }

        $studentInfo = StudentsInfo::where('userUid', $user->id)
            ->where('year', $year)
            ->first();

        $booking = null;
        if ($studentInfo && $studentInfo->bookingId) {
            $booking = Booking::with('residence:residence,id', 'roomType:name,id,property_id')
                ->where('id', $studentInfo->bookingId)
                ->where('userId', $user->id)
                ->where('year', $year)
                ->first();
        }

        if (!$booking) {
            $booking = Booking::with('residence:residence,id', 'roomType:name,id,property_id')
                ->where('userId', $user->id)
                ->where('year', $year)
                ->first();
        }

        $residence = null;
        $roomType = null;
        if ($booking) {
            $residence = $booking->relationLoaded('residence') ? $booking->getRelation('residence') : null;
            $residence = $residence ?: Residence::select('residence', 'id')->where('id', $booking->property)->first();
            $roomType = $booking->relationLoaded('roomType') ? $booking->getRelation('roomType') : null;
            $roomType = $roomType ?: $booking->roomType()->first();
        }
        $eligible = true;
        $reasons = [];

        if ((int) $user->userRole !== 0) {
            $eligible = false;
            $reasons[] = 'userRole is not student (0)';
        }
        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            $eligible = false;
            $reasons[] = 'user is banned';
        }
        if (!$studentInfo) {
            $eligible = false;
            $reasons[] = "no students_info row for {$year}";
        }
        if ($studentInfo && (int) $studentInfo->rob_60_cancelled === 1) {
            $eligible = false;
            $reasons[] = 'student application is cancelled';
        }
        if (!$booking) {
            $eligible = false;
            $reasons[] = "no booking for {$year}";
        }
        if ($booking && (int) $booking->cancelled === 1) {
            $eligible = false;
            $reasons[] = 'booking is cancelled';
        }
        if ($booking && empty($booking->bed_code)) {
            $eligible = false;
            $reasons[] = 'booking has no bed_code';
        }
        if ($booking && !$residence) {
            $eligible = false;
            $reasons[] = 'booking has no valid residence';
        }
        if ($booking && !$roomType) {
            $eligible = false;
            $reasons[] = 'booking has no valid room type';
        }

        $this->line('');
        $this->info('Laravel status');
        $this->line('User ID: ' . $user->id);
        $this->line('User email: ' . ($user->email ?? ''));
        $this->line('Student row: ' . ($studentInfo ? 'yes #' . $studentInfo->id : 'no'));
        $this->line('Booking row: ' . ($booking ? 'yes #' . $booking->id : 'no'));
        $this->line('Booking code: ' . ($booking->booking_code ?? ''));
        $this->line('Bed code: ' . ($booking->bed_code ?? ''));
        $this->line('Residence: ' . ($residence->residence ?? ''));
        $this->line('Room type: ' . ($roomType->name ?? ''));
        $this->line('Student cancelled: ' . ($studentInfo->rob_60_cancelled ?? ''));
        $this->line('Booking cancelled: ' . ($booking->cancelled ?? ''));
        $this->line('Final approval ROB: ' . ($studentInfo->rob_50_approve ?? ''));
        $this->line('Lease ROB: ' . ($studentInfo->rob_55_lease ?? ''));

        if ($eligible) {
            $this->info('Eligibility: eligible for current-year maintenance app registration.');
        } else {
            $this->error('Eligibility: blocked - ' . implode('; ', $reasons));
        }

        $this->showOpenFutureApplicationCheck($user, $year);
        $this->showRegistrationLogs($user->id, $year);
        $this->showFirebaseStatus($studentInfo->userEmail ?? $user->email ?? $identifier);
    }

    private function showOpenFutureApplicationCheck(User $user, string $currentYear): void
    {
        $futureYears = Lookups::uiYearLookups()
            ->pluck('lookup_name')
            ->map(function ($year) {
                return (string) (int) $year;
            })
            ->filter(function ($year) use ($currentYear) {
                return (int) $year > (int) $currentYear;
            })
            ->values();

        $this->line('');
        $this->info('Open future application check');

        if ($futureYears->isEmpty()) {
            $this->line('No future application year is currently open.');
            return;
        }

        foreach ($futureYears as $futureYear) {
            $status = $this->yearEligibilityForUser($user, $futureYear);

            $this->line("Future year: {$futureYear}");
            $this->line('Student row: ' . ($status['studentInfo'] ? 'yes #' . $status['studentInfo']->id : 'no'));
            $this->line('Booking row: ' . ($status['booking'] ? 'yes #' . $status['booking']->id : 'no'));
            $this->line('Bed code: ' . ($status['booking']->bed_code ?? ''));
            $this->line('Residence: ' . ($status['residence']->residence ?? ''));
            $this->line('Room type: ' . ($status['roomType']->name ?? ''));

            if ($status['eligible']) {
                $this->warn('Would otherwise be Firebase-ready, but must remain blocked until this becomes the current calendar year.');
            } else {
                $this->line('Not Firebase-ready: ' . implode('; ', $status['reasons']));
            }
        }
    }

    private function yearEligibilityForUser(User $user, string $year): array
    {
        $studentInfo = StudentsInfo::where('userUid', $user->id)
            ->where('year', $year)
            ->first();

        $booking = null;
        if ($studentInfo && $studentInfo->bookingId) {
            $booking = Booking::with('residence:residence,id', 'roomType:name,id,property_id')
                ->where('id', $studentInfo->bookingId)
                ->where('userId', $user->id)
                ->where('year', $year)
                ->first();
        }

        if (!$booking) {
            $booking = Booking::with('residence:residence,id', 'roomType:name,id,property_id')
                ->where('userId', $user->id)
                ->where('year', $year)
                ->first();
        }

        $residence = null;
        $roomType = null;
        if ($booking) {
            $residence = $booking->relationLoaded('residence') ? $booking->getRelation('residence') : null;
            $residence = $residence ?: Residence::select('residence', 'id')->where('id', $booking->property)->first();
            $roomType = $booking->relationLoaded('roomType') ? $booking->getRelation('roomType') : null;
            $roomType = $roomType ?: $booking->roomType()->first();
        }

        $reasons = [];
        if ((int) $user->userRole !== 0) {
            $reasons[] = 'userRole is not student (0)';
        }
        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            $reasons[] = 'user is banned';
        }
        if (!$studentInfo) {
            $reasons[] = "no students_info row for {$year}";
        }
        if ($studentInfo && (int) $studentInfo->rob_60_cancelled === 1) {
            $reasons[] = 'student application is cancelled';
        }
        if (!$booking) {
            $reasons[] = "no booking for {$year}";
        }
        if ($booking && (int) $booking->cancelled === 1) {
            $reasons[] = 'booking is cancelled';
        }
        if ($booking && empty($booking->bed_code)) {
            $reasons[] = 'booking has no bed_code';
        }
        if ($booking && !$residence) {
            $reasons[] = 'booking has no valid residence';
        }
        if ($booking && !$roomType) {
            $reasons[] = 'booking has no valid room type';
        }

        return [
            'eligible' => empty($reasons),
            'reasons' => $reasons,
            'studentInfo' => $studentInfo,
            'booking' => $booking,
            'residence' => $residence,
            'roomType' => $roomType,
        ];
    }

    private function findLocalUser(string $identifier): ?User
    {
        if (ctype_digit($identifier)) {
            return User::withTrashed()->where('id', (int) $identifier)->first();
        }

        $email = strtolower(trim($identifier));
        $user = User::withTrashed()
            ->where('email', $email)
            ->orWhere('userEmail', $email)
            ->first();

        if ($user) {
            return $user;
        }

        $studentUserId = StudentsInfo::withTrashed()
            ->where('userEmail', $email)
            ->orderByDesc('year')
            ->value('userUid');

        return $studentUserId ? User::withTrashed()->where('id', $studentUserId)->first() : null;
    }

    private function showRegistrationLogs(int $userId, string $year): void
    {
        $logs = FirebaseRegistrationLog::where('user_id', $userId)
            ->where('year', $year)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $this->line('');
        $this->info('Recent Firebase registration logs');
        if ($logs->isEmpty()) {
            $this->line('No logs found for this user/year.');
            return;
        }

        foreach ($logs as $log) {
            $this->line(sprintf(
                '#%s | %s | %s | %s | %s',
                $log->id,
                $log->created_at,
                $log->status,
                $log->stage,
                $log->message
            ));
        }
    }

    private function showFirebaseStatus(string $emailOrUid): void
    {
        $this->line('');
        $this->info('Firebase status');

        try {
            $firebaseUser = filter_var($emailOrUid, FILTER_VALIDATE_EMAIL)
                ? $this->firebase()->getUserByEmail($emailOrUid)
                : $this->firebase()->getUserByUid($emailOrUid);

            $this->line('Auth user: yes');
            $this->line('UID: ' . $firebaseUser->uid);
            $this->line('Email: ' . $firebaseUser->email);

            $profile = $this->firebase()->getUserProfileByUid($firebaseUser->uid);
            $this->line('Firestore profile: ' . ($profile ? 'yes' : 'no'));
            if ($profile) {
                $this->line('Firestore isActive: ' . json_encode($profile['isActive'] ?? null));
                $this->line('Firestore building: ' . ($profile['building'] ?? ''));
                $this->line('Firestore room: ' . ($profile['room'] ?? ''));
                $this->line('Firestore roomType: ' . ($profile['roomType'] ?? ''));
            }
        } catch (\Throwable $e) {
            $this->error('Firebase lookup failed: ' . $e->getMessage());
        }
    }
}
