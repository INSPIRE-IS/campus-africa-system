<?php

namespace Tests\Feature;

use App\Services\BedsReportDeliveryService;
use App\Support\BedsReportFilename;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BedsReportControllerTest extends TestCase
{
    public function test_a_valid_signed_link_shows_report_details_and_downloads_the_private_file(): void
    {
        Storage::fake('local');
        $filename = BedsReportFilename::make('2027', 'All', '20261124_184644');
        $path = BedsReportDeliveryService::STORAGE_DIRECTORY . '/' . $filename;
        Storage::disk('local')->put($path, 'report-content');

        list($createdAt, $expiresAt, $landingUrl) = $this->makeSignedUrls($filename);

        $response = $this->get($landingUrl)
            ->assertOk()
            ->assertSee($filename)
            ->assertSee(Carbon::createFromTimestamp($createdAt)->format('d M Y H:i T'))
            ->assertSee('Download report');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));

        $downloadUrl = URL::temporarySignedRoute('beds-report.download', $expiresAt, [
            'filename' => $filename,
            'created_at' => $createdAt,
        ]);

        $this->get($downloadUrl)
            ->assertOk()
            ->assertDownload($filename);
        $this->assertSame('report-content', Storage::disk('local')->get($path));
    }

    public function test_an_expired_signed_token_shows_expired_status_and_cannot_download(): void
    {
        Storage::fake('local');
        $filename = BedsReportFilename::make('2027', 'All', '20261124_184644');
        $path = BedsReportDeliveryService::STORAGE_DIRECTORY . '/' . $filename;
        Storage::disk('local')->put($path, 'report-content');

        $createdAt = now()->subHours(48)->subSecond()->getTimestamp();
        $expiresAt = Carbon::createFromTimestamp($createdAt)->addHours(48);
        $landingUrl = URL::temporarySignedRoute('beds-report.show', $expiresAt, [
            'filename' => $filename,
            'created_at' => $createdAt,
        ]);

        $this->get($landingUrl)
            ->assertOk()
            ->assertSee('This download link has expired')
            ->assertDontSee('Download report');

        $downloadUrl = URL::temporarySignedRoute('beds-report.download', $expiresAt, [
            'filename' => $filename,
            'created_at' => $createdAt,
        ]);

        $this->get($downloadUrl)->assertForbidden();
    }

    public function test_a_valid_link_shows_when_the_report_file_is_missing(): void
    {
        Storage::fake('local');
        $filename = BedsReportFilename::make('2027', 'All', '20261124_184644');
        list(, , $landingUrl) = $this->makeSignedUrls($filename);

        $this->get($landingUrl)
            ->assertOk()
            ->assertSee('This report file is no longer available')
            ->assertDontSee('Download report');
    }

    public function test_a_changed_filename_invalidates_the_signed_link(): void
    {
        $filename = BedsReportFilename::make('2027', 'All', '20261124_184644');
        list($createdAt, $expiresAt) = $this->makeSignedUrls($filename);
        $tamperedUrl = URL::temporarySignedRoute('beds-report.show', $expiresAt, [
            'filename' => $filename,
            'created_at' => $createdAt,
        ]);
        $tamperedUrl = str_replace($filename, 'Bed_Allocations_Export_2027_Other_20261124_184644.xlsx', $tamperedUrl);

        $this->get($tamperedUrl)->assertForbidden();
    }

    private function makeSignedUrls(string $filename): array
    {
        $createdAt = now()->getTimestamp();
        $expiresAt = Carbon::createFromTimestamp($createdAt)->addHours(48);
        $landingUrl = URL::temporarySignedRoute('beds-report.show', $expiresAt, [
            'filename' => $filename,
            'created_at' => $createdAt,
        ]);

        return [$createdAt, $expiresAt, $landingUrl];
    }
}
