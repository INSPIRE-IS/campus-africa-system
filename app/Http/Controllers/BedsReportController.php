<?php

namespace App\Http\Controllers;

use App\Services\BedsReportDeliveryService;
use App\Support\BedsReportFilename;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class BedsReportController extends Controller
{
    public function show(Request $request)
    {
        abort_unless(URL::hasCorrectSignature($request), 403);

        $filename = $this->filenameFromRequest($request);
        $createdTimestamp = $this->timestampFromRequest($request, 'created_at');
        $expiresTimestamp = $this->timestampFromRequest($request, 'expires');

        abort_unless(
            $expiresTimestamp === $createdTimestamp + (BedsReportDeliveryService::LINK_LIFETIME_HOURS * 3600),
            403
        );

        $createdAt = Carbon::createFromTimestamp($createdTimestamp);
        $expiresAt = Carbon::createFromTimestamp($expiresTimestamp);
        $expired = now()->getTimestamp() > $expiresTimestamp;
        $path = BedsReportDeliveryService::STORAGE_DIRECTORY . '/' . $filename;
        $reportExists = Storage::disk('local')->exists($path);
        $downloadUrl = null;

        if (!$expired && $reportExists) {
            $downloadUrl = URL::temporarySignedRoute('beds-report.download', $expiresAt, [
                'filename' => $filename,
                'created_at' => $createdTimestamp,
            ]);
        }

        return response()
            ->view('reports.beds-download', compact('filename', 'createdAt', 'expiresAt', 'expired', 'reportExists', 'downloadUrl'))
            ->header('Cache-Control', 'private, no-store, max-age=0')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function download(Request $request)
    {
        abort_unless(URL::hasValidSignature($request), 403);

        $filename = $this->filenameFromRequest($request);
        $createdTimestamp = $this->timestampFromRequest($request, 'created_at');
        $expiresTimestamp = $this->timestampFromRequest($request, 'expires');

        abort_unless(
            $expiresTimestamp === $createdTimestamp + (BedsReportDeliveryService::LINK_LIFETIME_HOURS * 3600),
            403
        );

        $path = BedsReportDeliveryService::STORAGE_DIRECTORY . '/' . $filename;

        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')
            ->download($path, $filename, [
                'Cache-Control' => 'private, no-store, max-age=0',
                'X-Robots-Tag' => 'noindex, nofollow',
            ]);
    }

    private function filenameFromRequest(Request $request): string
    {
        $filename = $request->query('filename');

        abort_unless(is_string($filename) && BedsReportFilename::isValid($filename), 404);

        return $filename;
    }

    private function timestampFromRequest(Request $request, string $key): int
    {
        $timestamp = filter_var($request->query($key), FILTER_VALIDATE_INT);

        abort_unless($timestamp !== false && $timestamp > 0, 403);

        return $timestamp;
    }
}
