<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Booking;
use App\Models\User;
use App\Models\UserFiles;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DownloadAllDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:docs {year} {prop} {lease_filter}';

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
        $debug = 0;

        $year = $this->argument('year');
        $prop = $this->argument('prop');
        $lease_filter = $this->argument('lease_filter');

        $bookings = Booking::where("year", $year)
            ->where("property", $prop)
            ->get();

        // $users = User::where("year", $year)
        //     ->get();
        $zipFileName = "all_user_files.zip";

        $password = "12345678";

        $zip = new ZipArchive();
        $zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $userZipFilesToDelete = []; // Array to store user ZIP file names

        foreach ($bookings as $booking) {
            $u = User::find($booking->userId);

            $file_ok = false;
            if ($lease_filter > 0) {
                $lease_file = UserFiles::where("userUid", $u->id)->where("year", $year)->where('status', $lease_filter == 2 ? 'Approved' : "Uploaded")->first();

                if (isset($lease_file)) {
                    $file_ok = true;
                }
            } else {
                $lease_file = true;
            }

            if (isset($u->id) && $file_ok == true) {
                $code = $booking->booking_code . " " . $booking->bed_code . " " . $u->name;

                $files = UserFiles::where("userUid", $u->id)->where("year", $year)->get();
                $userZipFileName = $code . ".zip";

                $filesToAdd = $files->filter(function ($file) {
                    $filePath = storage_path("app/public/" . $file->fileUrl);
                    return file_exists($filePath) && !is_dir($filePath);
                });

                if ($filesToAdd->isNotEmpty()) {
                    $debug == 1 && dump(
                        "Adding user " . $u->id . " with " . $filesToAdd->count() . " files"
                    );

                    $userZip = new ZipArchive();
                    if (
                        $userZip->open(
                            $userZipFileName,
                            ZipArchive::CREATE | ZipArchive::OVERWRITE
                        ) === true
                    ) {
                        foreach ($filesToAdd as $file) {
                            $filePath = storage_path("app/public/" . $file->fileUrl);
                            $fileNameInZip = basename($filePath);
                            $userZip->addFile($filePath, $fileNameInZip);
                        }
                        $userZip->close();

                        // Add the user ZIP into the combined ZIP
                        $zip->addFile($userZipFileName, $userZipFileName);
                        $userZipFilesToDelete[] = $userZipFileName; // Add to the list for deletion in next iteration
                    } else {
                        $debug == 1 && dump("Error creating user ZIP: " . $userZip->getStatusString());
                    }
                } else {
                    $debug == 1 && dump("User " . $u->id . " has no files to add.");
                }
            }
        }

        // Set password ONLY on the main ZIP file after all user ZIPs are added
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i); // Get stat info using index
            $zip->setEncryptionName($stat["name"], ZipArchive::EM_AES_256, $password);
        }

        $zip->close();

        $sourcePath = $zipFileName;
        $destinationPath = public_path($zipFileName); // Path in the public directory

        // Move the file using rename()
        if (rename($sourcePath, $destinationPath)) {
            $debug == 1 && dump(
                "Download all user files: <a href='/storage/" .
                    $zipFileName .
                    "'>" .
                    $zipFileName .
                    "</a><br>"
            );
        } else {
            $debug == 1 && dump("Error moving ZIP file to public directory."); // Handle potential errors
        }

        $debug == 1 && dump("Done");

        // Delete any remaining user ZIP files
        foreach ($userZipFilesToDelete as $zipToDelete) {
            @unlink($zipToDelete);
        }
    }
}
