<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use ZipArchive;

class DownloadSelectedDocs extends Command
{
    protected $signature = 'download:selected_docs {year} {institution} {student_type} {lease_filter} {email}';
    protected $description = 'Download selected user documents based on criteria';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $year = $this->argument('year');
        $prop = $this->argument('prop');
        $institution = $this->argument('institution');
        $student_type = $this->argument('student_type');
        $lease_filter = $this->argument('lease_filter');
        $email = $this->argument('email');
        $zipFileName = "selected_user_files.zip";
        $password = "12345678";

        $zip = new ZipArchive();
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Failed to create ZIP file.");
            return 1;
        }

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

        foreach ($users as $user) {
            $filePath = storage_path("app/public/" . $user->fileUrl);
            if (file_exists($filePath)) {
                $fileNameInZip = $user->userTitle . "_" . $user->userFirstName . "_" . $user->userLastname . "_" . basename($filePath);
                $zip->addFile($filePath, $fileNameInZip);
            }
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $zip->setEncryptionName($stat["name"], ZipArchive::EM_AES_256, $password);
        }

        $zip->close();

        $destinationPath = public_path($zipFileName);
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
