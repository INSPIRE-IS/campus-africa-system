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
        $year = (int) $this->argument('year');
        $prop = trim($this->argument('prop'));
        $institution = trim($this->argument('institution'));
        $student_type = trim($this->argument('student_type'));
        $lease_filter = (int) $this->argument('lease_filter');
        $email = filter_var($this->argument('email'), FILTER_VALIDATE_EMAIL);

        if (!$email) {
            $this->error("Invalid email address.");
            return 1;
        }

        $zipFileName = "selected_user_files.zip";
        $password = "12345678";
        $destinationPath = public_path($zipFileName);

        $users = DB::table('bookings_data as bd')
            ->join('students_info as si', 'si.bookingId', '=', 'bd.id')
            ->join('user_files as uf', 'uf.userUid', '=', 'si.userUid')
            ->where("bd.property", $prop)
            ->where('bd.year', $year)
            ->where('si.institutionStudy', $institution)
            ->where('si.studentType', $student_type)
            ->where('uf.status', $lease_filter == 2 ? 'Approved' : "Uploaded")
            ->where('uf.fileId', 'moveIn')
            ->select('bd.userId', 'uf.fileName', 'si.userTitle', 'si.userFirstName', 'si.userLastname', 'uf.fileUrl')
            ->get();

        if ($users->isEmpty()) {
            Mail::raw("No documents found for the given criteria.", function ($message) use ($email) {
                $message->to($email)->subject('No Documents Available');
            });
            $this->info("No documents found. Email sent to: {$email}");
            return 0;
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
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

        if (!is_writable(public_path())) {
            $this->error("Public directory is not writable.");
            return 1;
        }

        if (!rename($zipFileName, $destinationPath)) {
            $this->error("Error moving ZIP file to public directory.");
            return 1;
        }

        Mail::raw("Zip file created - https://apply.campusafrica.co.za/{$zipFileName}", function ($message) use ($email) {
            $message->to($email)->subject('Your Document Download');
        });

        $this->info("Download link sent to: {$email}");
        return 0;
    }
}
