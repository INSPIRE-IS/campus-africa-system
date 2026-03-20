<?php

namespace App\Console\Commands;

use App\Models\ActionList;
use App\Models\BookingPayment;
use App\Models\UserFiles;
use App\Models\MailerList;
use App\Models\Lookups;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendMonthlyPopMail;

class SendMonthlyPopNotifications extends Command
{
    protected int $sendDay = 25; // Change for testing if needed.

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:send-monthly-pop {--date=} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly POP email reminders and ensure the monthly POP document exists.';

    public function handle()
    {
        $dateOption = $this->option('date');
        $dryRun = (bool) $this->option('dry-run');
        $today = $dateOption ? Carbon::parse($dateOption)->toDateString() : Carbon::today()->toDateString();

        $mailer = MailerList::where('mailer_name', 'Monthly POP Reminder')->first();
        if (!$mailer || $mailer->status != '1') {
            $this->warn('Monthly POP Reminder mailer is missing or disabled.');
            return 0;
        }

        $sendDay = $this->sendDay;
        $todayCarbon = Carbon::parse($today);
        if ((int) $todayCarbon->day !== $sendDay) {
            $this->info('Monthly POP reminders only send on day 25.');
            return 0;
        }

        $rows = DB::table('booking_payments as bp')
            ->join('bookings_data as b', 'b.id', '=', 'bp.bookingId')
            ->join('students_info as si', function ($join) {
                $join->on('si.userUid', '=', 'b.userId')
                    ->on('si.year', '=', 'b.year');
            })
            ->whereNotNull('bp.monthly_pop_amount')
            ->where(function ($query) {
                $query->where('bp.send_to_student', 1)
                    ->orWhere('bp.send_to_sponsor', 1);
            })
            ->select(
                'bp.id as booking_payment_id',
                'bp.monthly_pop_amount',
                'bp.send_to_student',
                'bp.send_to_sponsor',
                'b.id as booking_id',
                'b.userId as user_id',
                'b.year as year',
                'b.booking_code',
                'b.cancelled as booking_cancelled',
                'b.lease_from_date',
                'b.lease_to_date',
                'si.userFirstName',
                'si.userLastname',
                'si.userEmail',
                'si.sponEmail',
                'si.rob_60_cancelled'
            )
            ->get();

        $counts = [
            'processed' => 0,
            'created_docs' => 0,
            'emails_sent' => 0,
            'skipped' => 0,
        ];

        foreach ($rows as $row) {
            $counts['processed']++;

            if ((int) $row->rob_60_cancelled === 1 || (string) $row->booking_cancelled === '1') {
                $counts['skipped']++;
                continue;
            }

            if (!empty($row->lease_from_date)) {
                $leaseFrom = Carbon::parse($row->lease_from_date)->startOfDay();
                if ($today < $leaseFrom->toDateString()) {
                    $counts['skipped']++;
                    continue;
                }
            }

            if (!empty($row->lease_to_date)) {
                $leaseTo = Carbon::parse($row->lease_to_date)->endOfDay();
                if ($today > $leaseTo->toDateString()) {
                    $counts['skipped']++;
                    continue;
                }
            }

            $targetDay = $sendDay;

            $monthKey = Carbon::parse($today)->format('Y-m');
            $alreadySent = ActionList::where('userUid', $row->user_id)
                ->where('year', $row->year)
                ->where('action', 'like', "Monthly POP notice sent {$monthKey}%")
                ->exists();

            if ($alreadySent) {
                $counts['skipped']++;
                continue;
            }

            $existing = UserFiles::withTrashed()
                ->where('userUid', $row->user_id)
                ->where('fileId', 'other_monthly_pop')
                ->where('year', $row->year)
                ->first();

            if (!$existing && !$dryRun) {
                UserFiles::create([
                    'userUid' => $row->user_id,
                    'fileId' => 'other_monthly_pop',
                    'year' => $row->year,
                ]);
                $counts['created_docs']++;
            }

            $effectiveSendDate = $todayCarbon->copy();
            $effectiveDueDate = $effectiveSendDate->copy()->addMonthNoOverflow()->startOfMonth()->toDateString();
            $effectiveSendDateString = $effectiveSendDate->toDateString();

            $sendStudent = (int) $row->send_to_student === 1;
            $sendSponsor = (int) $row->send_to_sponsor === 1;

            if ($dryRun) {
                if ($sendStudent || $sendSponsor) {
                    $counts['emails_sent']++;
                }
                continue;
            }

            $appUrl = rtrim(config('app.url') ?: 'https://apply.campusafrica.co.za', '/');
            $messageReplace = [
                '[application_number]' => $row->booking_code,
                '[name]' => $row->userFirstName,
                '[monthly_pop_amount]' => $row->monthly_pop_amount,
                '[monthly_pop_due_date]' => $effectiveDueDate,
                '[application_link]' => "<a href='{$appUrl}/login' target='_blank'>",
                '[close_application_link]' => '</a>',
            ];

            $message = str_replace(array_keys($messageReplace), array_values($messageReplace), $mailer->message);
            $subject = str_replace('[application_number]', $row->booking_code, $mailer->subject);

            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
            $cc = !empty($allowedMailsCC) ? explode(';', $allowedMailsCC) : [];
            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

            if ($sendStudent) {
                $studentEmail = $this->resolveAllowedEmail($row->userEmail, $allowedMails);
                SendMonthlyPopMail::dispatch($studentEmail, $subject, $message, $cc);
                $counts['emails_sent']++;
            }

            if ($sendSponsor && !empty($row->sponEmail)) {
                $sponsorEmail = $this->resolveAllowedEmail($row->sponEmail, $allowedMails);
                SendMonthlyPopMail::dispatch($sponsorEmail, $subject, $message, $cc);
                $counts['emails_sent']++;
            }

            if ($sendStudent || $sendSponsor) {
                ActionList::create([
                    'userUid' => $row->user_id,
                    'action' => "Monthly POP notice sent {$monthKey} (send {$effectiveSendDateString}, due {$effectiveDueDate}, amount {$row->monthly_pop_amount})",
                    'actionUserUid' => 0,
                    'actionUserDisplayName' => 'System',
                    'actionUserRole' => 'system',
                    'year' => $row->year,
                ]);
            }
        }

        Log::info('booking:send-monthly-pop completed', $counts);
        $this->info(json_encode($counts));

        return 0;
    }

    private function sendMail($email, $name, $message, $subject)
    {
        $data['name'] = $name ?: 'Student';
        $data['email'] = $email;
        $data['messageForMail'] = $message;
        $data['subject'] = $subject;
        $data['cc'] = [];

        Mail::send('email.yellow_back', $data, function ($mail) use ($data) {
            $mail->to($data['email'])->subject($data['subject']);
            $mail->from('noreply@campusafrica.co.za', 'Campus Africa');
        });
    }

    private function resolveAllowedEmail($email, $allowedMails)
    {
        if (empty($allowedMails)) {
            return $email;
        }
        $mailArr = explode(';', $allowedMails);
        $domain = explode('@', $email)[1] ?? '';
        if (in_array($domain, $mailArr, true)) {
            return $email;
        }
        return 'studentsca00@gmail.com';
    }
}
