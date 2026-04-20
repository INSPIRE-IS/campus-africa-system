<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use ZipArchive;

class DownloadSelectedDocs extends Command
{
    protected $signature = 'download:selected_docs {year} {prop} {institution} {student_type} {lease_filter} {email}';
    protected $description = 'Download selected user documents based on criteria';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $yearArg = trim($this->argument('year'));
        $prop = trim($this->argument('prop'));
        $institution = trim($this->argument('institution'));
        $student_type = trim($this->argument('student_type'));
        $lease_filter = (int) $this->argument('lease_filter');
        $email = filter_var($this->argument('email'), FILTER_VALIDATE_EMAIL);

        if ($yearArg === '' || !is_numeric($yearArg)) {
            $this->error("Invalid year selected.");
            return 1;
        }
        $year = (int) $yearArg;

        if (!$email) {
            $this->error("Invalid email address.");
            return 1;
        }

        $zipFileName = "selected_user_files.zip";
        $password = "12345678";
        $zipPath = storage_path("app/public/" . $zipFileName);

        $query = DB::table('bookings_data as bd')
            ->join('students_info as si', function ($join) use ($year) {
                $join->on('si.bookingId', '=', 'bd.id')
                    ->where('si.year', $year);
            })
            ->join('user_files as uf', function ($join) use ($year) {
                $join->on('uf.userUid', '=', 'si.userUid')
                    ->where('uf.year', $year);
            })
            ->where('bd.year', $year)
            ->where('uf.fileId', 'moveIn');

        if ($prop !== '' && $prop !== 'All') {
            $query->where('bd.property', $prop);
        }

        if ($institution !== '' && $institution !== 'All') {
            $query->where('si.institutionStudy', $institution);
        }

        if ($student_type !== '' && $student_type !== 'All') {
            $query->where('si.studentType', $student_type);
        }

        if ($lease_filter === 2) {
            $query->where('uf.status', 'Approved');
        } elseif ($lease_filter === 1) {
            $query->where('uf.status', 'Uploaded');
        } elseif ($lease_filter === 0) {
            $users = collect();
        }

        if (!isset($users)) {
            $users = $query
                ->select('bd.userId', 'uf.fileName', 'si.userTitle', 'si.userFirstName', 'si.userLastname', 'uf.fileUrl')
                ->get();
        }

        if ($users->isEmpty()) {
            Mail::raw("No documents found for the given criteria.", function ($message) use ($email) {
                $message->to($email)->subject('No Documents Available');
            });
            $this->info("No documents found. Email sent to: {$email}");
            return 0;
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Failed to create ZIP file.");
            return 1;
        }

        foreach ($users as $user) {
            $filePath = Storage::path("public/" . $user->fileUrl);
            if (file_exists($filePath)) {
                $fileNameInZip = "{$user->userTitle}_{$user->userFirstName}_{$user->userLastname}_" . basename($filePath);
                $zip->addFile($filePath, $fileNameInZip);
            }
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $zip->setEncryptionName($stat["name"], ZipArchive::EM_AES_256, $password);
        }

        $zip->close();

        Mail::raw("Zip file created - https://apply.campusafrica.co.za/storage/{$zipFileName}", function ($message) use ($email) {
            $message->to($email)->subject('Your Document Download');
        });

        $this->info("Download link sent to: {$email}");
        return 0;
    }
}
