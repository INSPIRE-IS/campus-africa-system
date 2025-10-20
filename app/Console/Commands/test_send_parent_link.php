<?php

namespace App\Console\Commands;

use App\Jobs\SendParentLeaseMail;
use App\Models\StudentsInfo;
use App\Models\User;
use App\Models\UserLeaseToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class test_send_parent_link extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test_send_parent_link {userId} {year}';
    // Usage: php artisan command:test_send_parent_link 11117 2025

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
        $userUid = $this->argument('userId');
        $year = $this->argument('year');
        $signed_at = 'Regenerated via CLI';

        $lease_link = UserLeaseToken::where('userUid', $userUid)
            ->where('active', 0)
            ->where('signed', 1)
            ->where('role', 'student')
            ->where('year', $year)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lease_link) {
            Log::error('No lease link found for user ' . $userUid . ' year ' . $year);
            return;
        }

        $user = User::where('id', $userUid)->first();

        // Create Parent Lease Token
        $parentLeaseToken = UserLeaseToken::create([
            "userUid"   => $userUid,
            "leaseToken" => $this->generateRandomLongString(),
            "active"    => 1,
            "signed"    => 0,
            "role"      => "parent", // you may want to add a column to distinguish tokens
            "year" => $year
        ]);

        // Build link for parent
        $baseurl = config('app.url');

        $leaseOpenTag = "<a href='" . $baseurl . "/sign-lease/" . $parentLeaseToken->leaseToken . "' target='_blank'>";
        Log::info('Sending parent signing link: ' . $leaseOpenTag);
        $studentInfo = StudentsInfo::where('userUid', $userUid)->where('year', $year)->first();

        // Find parent email (assuming in StudentsInfo or related table)
        $parentEmail = $studentInfo->sponEmail ?? null;
        $this->info("Parent email is {$parentEmail}");

        if ($parentEmail && $parentEmail !== "") {
            try {
                Log::info('Sending parent signing link: ' . $leaseOpenTag);
                dispatch(new SendParentLeaseMail($user, $this->generateRandomLongString(), $leaseOpenTag, $lease_link->year));
            } catch (\Exception $e) {
                Log::error('Failed to dispatch SendParentLeaseMail job', [
                    'error' => $e->getMessage(),
                    'user'  => $user,
                ]);
            }
        } else {
            Log::error('Failed to dispatch SendParentLeaseMail job', [
                'error' => 'Parent email not found',
                'user'  => $user,
                'leaseOpenTag'  => $leaseOpenTag,
            ]);
        }
    }


    public function generateRandomLongString($length = 24)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
