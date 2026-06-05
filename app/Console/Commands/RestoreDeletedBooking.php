<?php

namespace App\Console\Commands;

use App\Models\ActionList;
use App\Models\Booking;
use App\Models\Lookups;
use App\Models\Property;
use App\Models\StudentsInfo;
use App\Models\User;
use App\Models\UserFiles;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RestoreDeletedBooking extends Command
{
    protected $signature = 'booking:restore-deleted
        {userId : users.id / students_info.userUid}
        {--year= : Application year to inspect/restore}
        {--booking-id= : Restore one specific booking id when multiple exist}
        {--restore : Apply the restore. Without this flag the command is dry-run only}
        {--restore-user : Also restore the users row if it is soft-deleted}
        {--without-files : Do not restore soft-deleted user_files rows}
        {--reason=Restored soft-deleted booking via artisan command : ActionList reason for actual restore}';

    protected $description = 'Audit and optionally restore a soft-deleted booking, students_info row, and related files.';

    public function handle()
    {
        $rawUserId = $this->argument('userId');
        $userId = is_numeric($rawUserId) ? (int) $rawUserId : 0;
        $year = $this->option('year');
        $bookingId = $this->option('booking-id');
        $applyRestore = (bool) $this->option('restore');
        $restoreUser = (bool) $this->option('restore-user');
        $restoreFiles = !(bool) $this->option('without-files');

        if ($userId <= 0 && !empty($bookingId)) {
            $bookingForUser = Booking::withTrashed()->where('id', $bookingId)->first();
            if ($bookingForUser) {
                $userId = (int) $bookingForUser->userId;
                $this->line("Resolved userId {$userId} from booking-id {$bookingId}.");
            }
        }

        if ($userId <= 0) {
            $this->error('Provide the student user id, or provide --booking-id so the command can resolve it.');
            return 1;
        }

        if ($applyRestore && empty($year)) {
            $this->error('Restore requires --year so the wrong application year cannot be restored.');
            return 1;
        }

        $user = User::withTrashed()->where('id', $userId)->first();
        if (!$user) {
            $this->error("No users row found for id {$userId}.");
            return 1;
        }

        $this->line($applyRestore ? 'Mode: RESTORE' : 'Mode: DRY RUN');
        $this->line("User: {$user->id} | {$user->userFirstName} {$user->userLastname} | {$user->email}");
        $this->line('User deleted_at: ' . ($user->deleted_at ?: 'not deleted'));

        $bookings = $this->bookingQuery($userId, $year, $bookingId)->get();
        $students = $this->studentInfoQuery($userId, $year)->get();
        $files = $this->filesQuery($userId, $year)->get();

        $this->printBookings($bookings);
        $this->printStudentInfos($students);
        $this->printFiles($files);
        $this->printBedChecks($bookings, $userId);
        $nearbyActionCount = $this->printNearbyActions($userId, $bookings, $students, $files);
        $this->printRestoreReview($user, $bookings, $students, $files, $nearbyActionCount);
        $this->printManualFollowUps($user, $bookings, $students, $files, $nearbyActionCount, $restoreFiles, false);

        if (!$applyRestore) {
            $this->info('Dry run complete. No records changed.');
            $restoreCommand = 'php artisan booking:restore-deleted ' . $userId . ' --year=' . ($year ?: 'YYYY');
            if (!empty($bookingId)) {
                $restoreCommand .= ' --booking-id=' . $bookingId;
            }
            $restoreCommand .= ' --restore';
            $this->line('To restore: ' . $restoreCommand);
            if ($user->trashed()) {
                $this->warn('The users row is soft-deleted. Add --restore-user if the login account must also be restored.');
            }
            return 0;
        }

        if ($bookings->isEmpty()) {
            $this->error('No booking row matched the restore criteria.');
            return 1;
        }

        if ($bookings->count() > 1 && empty($bookingId)) {
            $this->error('Multiple booking rows matched. Re-run with --booking-id=ID.');
            return 1;
        }

        if ($user->trashed() && !$restoreUser) {
            $this->error('The users row is soft-deleted. Re-run with --restore-user if you intend to restore the login account too.');
            return 1;
        }

        $booking = $bookings->first();
        $restored = DB::transaction(function () use ($userId, $year, $booking, $restoreFiles, $restoreUser) {
            $userRestored = 0;
            if ($restoreUser) {
                $userRestored = User::withTrashed()->where('id', $userId)->restore();
            }

            $bookingRestored = 0;
            if ($booking->trashed()) {
                $bookingRestored = Booking::withTrashed()->where('id', $booking->id)->restore();
            }

            $studentRestored = StudentsInfo::withTrashed()
                ->where('userUid', $userId)
                ->where('year', $year)
                ->restore();

            $filesRestored = 0;
            if ($restoreFiles) {
                $filesRestored = UserFiles::withTrashed()
                    ->where('userUid', $userId)
                    ->where('year', $year)
                    ->restore();
            }

            return [
                'users' => $userRestored,
                'booking' => $bookingRestored,
                'students_info' => $studentRestored,
                'user_files' => $filesRestored,
            ];
        });

        ActionList::create([
            'userUid' => $userId,
            'action' => $this->option('reason') . ' | booking_id=' . $booking->id . ' | restored=' . json_encode($restored),
            'actionUserUid' => null,
            'actionUserDisplayName' => 'System',
            'actionUserRole' => 'system',
            'year' => $year,
        ]);

        $this->info('Restore complete.');
        $this->table(['Record type', 'Rows restored'], [
            ['users', $restored['users']],
            ['bookings_data', $restored['booking']],
            ['students_info', $restored['students_info']],
            ['user_files', $restored['user_files']],
        ]);

        $postUser = User::withTrashed()->where('id', $userId)->first();
        $postBookings = $this->bookingQuery($userId, $year, $booking->id)->get();
        $postStudents = $this->studentInfoQuery($userId, $year)->get();
        $postFiles = $this->filesQuery($userId, $year)->get();
        $this->printManualFollowUps($postUser, $postBookings, $postStudents, $postFiles, $nearbyActionCount, $restoreFiles, true);

        $this->warn('Restore finished. Use the table above for anything not restored automatically or still needing admin review.');
        return 0;
    }

    private function bookingQuery($userId, $year = null, $bookingId = null)
    {
        $query = Booking::withTrashed()
            ->where('userId', $userId)
            ->orderByDesc('year')
            ->orderByDesc('id');

        if (!empty($year)) {
            $query->where('year', $year);
        }

        if (!empty($bookingId)) {
            $query->where('id', $bookingId);
        }

        return $query;
    }

    private function studentInfoQuery($userId, $year = null)
    {
        $query = StudentsInfo::withTrashed()
            ->where('userUid', $userId)
            ->orderByDesc('year')
            ->orderByDesc('id');

        if (!empty($year)) {
            $query->where('year', $year);
        }

        return $query;
    }

    private function filesQuery($userId, $year = null)
    {
        $query = UserFiles::withTrashed()
            ->where('userUid', $userId)
            ->orderByDesc('year')
            ->orderBy('fileId');

        if (!empty($year)) {
            $query->where('year', $year);
        }

        return $query;
    }

    private function printBookings($bookings)
    {
        $rows = $bookings->map(function ($booking) {
            return [
                $booking->id,
                $booking->year,
                $booking->booking_code,
                $booking->deleted_at ?: 'not deleted',
                is_null($booking->cancelled) ? 'null' : $booking->cancelled,
                $booking->property,
                $booking->roomType,
                $booking->property_data_id ?: 'null',
                $booking->bed_code ?: 'null',
            ];
        })->all();

        $this->table(['booking_id', 'year', 'code', 'deleted_at', 'cancelled', 'property', 'roomType', 'property_data_id', 'bed_code'], $rows);
    }

    private function printStudentInfos($students)
    {
        $rows = $students->map(function ($student) {
            return [
                $student->id,
                $student->year,
                $student->bookingId ?: 'null',
                $student->deleted_at ?: 'not deleted',
                is_null($student->rob_60_cancelled) ? 'null' : $student->rob_60_cancelled,
                is_null($student->rob_40_bed) ? 'null' : $student->rob_40_bed,
                is_null($student->rob_50_approve) ? 'null' : $student->rob_50_approve,
            ];
        })->all();

        $this->table(['students_info_id', 'year', 'bookingId', 'deleted_at', 'rob_60_cancelled', 'rob_40_bed', 'rob_50_approve'], $rows);
    }

    private function printFiles($files)
    {
        $rows = $files->map(function ($file) {
            return [
                $file->id,
                $file->year,
                $file->fileId,
                $file->status,
                $file->deleted_at ?: 'not deleted',
            ];
        })->all();

        $this->table(['file_id', 'year', 'fileId', 'status', 'deleted_at'], $rows);
    }

    private function printBedChecks($bookings, $userId)
    {
        $rows = [];

        foreach ($bookings as $booking) {
            [$status, $details] = $this->bedStatusForBooking($booking, $userId);
            $rows[] = [$booking->id, $status, $details];
        }

        $this->table(['booking_id', 'bed status', 'details'], $rows);
    }

    private function bedStatusForBooking($booking, $userId)
    {
        if (empty($booking->property_data_id)) {
            return ['missing property_data_id', 'Bed cannot be inferred from booking row.'];
        }

        $property = Property::withTrashed()->where('id', $booking->property_data_id)->first();
        if (!$property) {
            return ['property_data missing', 'No matching property_data row.'];
        }

        $assignedTo = $property->user_id_room;
        $status = 'allocated to another id';
        if (empty($assignedTo)) {
            $status = 'appears unallocated';
        } elseif ((string) $assignedTo === (string) $booking->id || (string) $assignedTo === (string) $userId) {
            $status = 'appears assigned to this booking/student';
        }

        return [
            $status,
            'property_data_id=' . $property->id .
                '; deleted_at=' . ($property->deleted_at ?: 'not deleted') .
                '; room_available=' . (is_null($property->room_available) ? 'null' : $property->room_available) .
                '; user_id_room=' . (is_null($assignedTo) ? 'null' : $assignedTo) .
                '; bed_code=' . ($property->bed_code ?: 'null'),
        ];
    }

    private function printNearbyActions($userId, $bookings, $students, $files)
    {
        $deletedDates = collect()
            ->merge($bookings->pluck('deleted_at'))
            ->merge($students->pluck('deleted_at'))
            ->merge($files->pluck('deleted_at'))
            ->filter();

        if ($deletedDates->isEmpty()) {
            $this->line('No deleted_at timestamps found for nearby ActionList lookup.');
            return 0;
        }

        $firstDeleted = Carbon::parse($deletedDates->sort()->first());
        $from = $firstDeleted->copy()->subDays(2)->startOfDay();
        $to = $firstDeleted->copy()->addDays(2)->endOfDay();

        $actions = ActionList::where('userUid', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        $rows = $actions->map(function ($action) {
            return [
                $action->created_at,
                $action->actionUserUid ?: 'null',
                $action->actionUserDisplayName,
                $action->action,
            ];
        })->all();

        $this->line('Nearby ActionList entries around first deleted_at (' . $firstDeleted . '):');
        $this->table(['created_at', 'actor_id', 'actor_name', 'action'], $rows);

        return $actions->count();
    }

    private function printRestoreReview($user, $bookings, $students, $files, $nearbyActionCount)
    {
        $rows = [];

        $rows[] = [
            'User account',
            $user->trashed() ? 'Needs restore-user' : 'OK',
            $user->trashed() ? 'users.deleted_at is set.' : 'users row is not deleted.',
        ];

        $deletedBookings = $bookings->filter(function ($booking) {
            return !empty($booking->deleted_at);
        });
        $rows[] = [
            'Booking row',
            $deletedBookings->isNotEmpty() ? 'Needs restore' : 'OK',
            $deletedBookings->isNotEmpty()
                ? $deletedBookings->count() . ' matching booking row(s) are soft-deleted.'
                : 'No matching booking row is soft-deleted.',
        ];

        $deletedStudents = $students->filter(function ($student) {
            return !empty($student->deleted_at);
        });
        $rows[] = [
            'Student info row',
            $deletedStudents->isNotEmpty() ? 'Needs restore' : 'OK',
            $deletedStudents->isNotEmpty()
                ? $deletedStudents->count() . ' matching students_info row(s) are soft-deleted.'
                : 'No matching students_info row is soft-deleted.',
        ];

        $deletedFiles = $files->filter(function ($file) {
            return !empty($file->deleted_at);
        });
        $rows[] = [
            'Documents/files',
            $deletedFiles->isNotEmpty() ? 'Needs restore' : 'OK',
            $deletedFiles->isNotEmpty()
                ? $deletedFiles->count() . ' year-specific file row(s) are soft-deleted.'
                : 'No matching file row is soft-deleted.',
        ];

        $bookingIds = $bookings->pluck('id')->map(function ($id) {
            return (string) $id;
        })->all();
        $bookingIdMismatch = $students->filter(function ($student) use ($bookingIds) {
            return !empty($student->bookingId) && !in_array((string) $student->bookingId, $bookingIds, true);
        });
        $rows[] = [
            'Student booking link',
            $bookingIdMismatch->isNotEmpty() ? 'Review' : 'OK',
            $bookingIdMismatch->isNotEmpty()
                ? 'students_info.bookingId does not match the inspected booking row.'
                : 'students_info.bookingId matches the inspected booking row where present.',
        ];

        $cancelledBookings = $bookings->filter(function ($booking) {
            return (string) $booking->cancelled === '1';
        });
        $cancelledStudents = $students->filter(function ($student) {
            return (string) $student->rob_60_cancelled === '1';
        });
        $rows[] = [
            'Cancelled state',
            ($cancelledBookings->isNotEmpty() || $cancelledStudents->isNotEmpty()) ? 'Review' : 'OK',
            ($cancelledBookings->isNotEmpty() || $cancelledStudents->isNotEmpty())
                ? 'Record is also cancelled. Use normal restore booking flow after soft-delete restore if needed.'
                : 'Not marked cancelled in inspected rows.',
        ];

        $bedIssues = $bookings->filter(function ($booking) {
            if (empty($booking->property_data_id)) {
                return true;
            }

            $property = Property::withTrashed()->where('id', $booking->property_data_id)->first();
            if (!$property) {
                return true;
            }

            if (empty($property->user_id_room)) {
                return false;
            }

            return (string) $property->user_id_room !== (string) $booking->id
                && (string) $property->user_id_room !== (string) $booking->userId;
        });
        $rows[] = [
            'Bed allocation',
            $bedIssues->isNotEmpty() ? 'Review' : 'OK',
            $bedIssues->isNotEmpty()
                ? 'Bed data is missing or appears allocated elsewhere. Do not assume bed restored.'
                : 'Bed appears unallocated or assigned to this booking/student.',
        ];

        $rows[] = [
            'Activity log',
            $nearbyActionCount > 0 ? 'Partial evidence' : 'Missing',
            $nearbyActionCount > 0
                ? $nearbyActionCount . ' nearby ActionList row(s) found, but delete booking itself does not log actor.'
                : 'No nearby ActionList rows found; delete booking itself does not log actor.',
        ];

        $this->line('Restore review / missing information:');
        $this->table(['Area', 'Status', 'Detail'], $rows);
    }

    private function printManualFollowUps($user, $bookings, $students, $files, $nearbyActionCount, $restoreFiles, $restoreApplied)
    {
        $rows = [];

        $deletedBookings = $bookings->filter(function ($booking) {
            return !empty($booking->deleted_at);
        });
        $deletedStudents = $students->filter(function ($student) {
            return !empty($student->deleted_at);
        });
        $deletedFiles = $files->filter(function ($file) {
            return !empty($file->deleted_at);
        });

        $rows[] = [
            'Original delete actor',
            'Cannot identify from app log',
            $nearbyActionCount > 0
                ? 'Nearby ActionList rows exist, but /delete/booking itself did not log who clicked delete.'
                : 'No nearby ActionList rows found; /delete/booking itself did not log who clicked delete.',
        ];

        $rows[] = [
            'Booking/application rows',
            ($deletedBookings->isEmpty() && $deletedStudents->isEmpty()) ? 'Restored/OK' : ($restoreApplied ? 'Still soft-deleted' : 'Will restore with --restore'),
            'bookings_data soft-deleted=' . $deletedBookings->count() . '; students_info soft-deleted=' . $deletedStudents->count(),
        ];

        if (!$restoreFiles) {
            $fileStatus = 'Not restored by request';
            $fileDetail = '--without-files was used; restore documents separately if needed.';
        } elseif ($deletedFiles->isEmpty()) {
            $fileStatus = 'Restored/OK';
            $fileDetail = 'No inspected user_files rows remain soft-deleted.';
        } else {
            $fileStatus = $restoreApplied ? 'Still soft-deleted' : 'Will restore with --restore';
            $fileDetail = $deletedFiles->count() . ' inspected user_files row(s) are soft-deleted.';
        }
        $rows[] = ['Documents/files', $fileStatus, $fileDetail];

        $cancelledDetails = [];
        foreach ($bookings as $booking) {
            $cancelledDetails[] = 'booking ' . $booking->id . '.cancelled=' . (is_null($booking->cancelled) ? 'null' : $booking->cancelled);
        }
        foreach ($students as $student) {
            $cancelledDetails[] = 'students_info ' . $student->id . '.rob_60_cancelled=' . (is_null($student->rob_60_cancelled) ? 'null' : $student->rob_60_cancelled);
        }
        $rows[] = [
            'ROB/cancelled status',
            'Not changed by this command',
            $cancelledDetails ? implode('; ', $cancelledDetails) : 'No rows inspected.',
        ];

        $bedDetails = [];
        foreach ($bookings as $booking) {
            [$status, $details] = $this->bedStatusForBooking($booking, (int) $booking->userId);
            $bedDetails[] = 'booking ' . $booking->id . ': ' . $status . ' (' . $details . ')';
        }
        $rows[] = [
            'Bed assignment',
            'Inspected only',
            $bedDetails ? implode('; ', $bedDetails) : 'No booking rows inspected. This command does not write property_data or allocate beds.',
        ];

        $rows[] = [
            'User login account',
            ($user && !$user->trashed()) ? 'OK' : 'Needs --restore-user',
            ($user && !$user->trashed())
                ? 'users row is active.'
                : 'users row is soft-deleted; this command restores it only with --restore-user.',
        ];

        if ($restoreApplied) {
            $rows[] = [
                'Restore activity log',
                'Created',
                'This command writes a new ActionList restore entry. It cannot reconstruct the original delete actor.',
            ];
        }

        $this->line($restoreApplied ? 'Not restored automatically / still needs review after restore:' : 'Not restored automatically / will need review:');
        $this->table(['Area', 'Status', 'Detail'], $rows);
    }
}
