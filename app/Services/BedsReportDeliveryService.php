<?php

namespace App\Services;

use App\Support\BedsReportFilename;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class BedsReportDeliveryService
{
    public const STORAGE_DIRECTORY = 'reports/bed-allocations';
    public const ATTACHMENT_LIMIT_BYTES = 1048576;
    public const LINK_LIFETIME_HOURS = 48;

    public function emailReport(string $filePath, string $filename, string $email): void
    {
        if (!BedsReportFilename::isValid($filename)) {
            throw new RuntimeException('Invalid beds report filename.');
        }

        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException('Beds report file is missing or unreadable.');
        }

        $fileSize = filesize($filePath);
        $createdTimestamp = filemtime($filePath);

        if ($fileSize === false || $createdTimestamp === false) {
            throw new RuntimeException('Unable to inspect the generated beds report file.');
        }

        if ($fileSize < self::ATTACHMENT_LIMIT_BYTES) {
            Mail::raw("The beds report is attached to this email: {$filename}", function ($message) use ($email, $filePath, $filename) {
                $message->to($email)
                    ->subject('Beds report download')
                    ->attach($filePath, [
                        'as' => $filename,
                        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
            });

            return;
        }

        $createdAt = Carbon::createFromTimestamp($createdTimestamp);
        $expiresAt = $createdAt->copy()->addHours(self::LINK_LIFETIME_HOURS);
        $url = URL::temporarySignedRoute('beds-report.show', $expiresAt, [
            'filename' => $filename,
            'created_at' => $createdTimestamp,
        ]);

        $messageBody = implode("\n", [
            'The beds report is ready.',
            '',
            "File: {$filename}",
            'Created: ' . $createdAt->format('d M Y H:i T'),
            'Link expires: ' . $expiresAt->format('d M Y H:i T'),
            '',
            'Open the report page to download the file:',
            $url,
        ]);

        Mail::raw($messageBody, function ($message) use ($email) {
            $message->to($email)->subject('Beds report download');
        });
    }
}
