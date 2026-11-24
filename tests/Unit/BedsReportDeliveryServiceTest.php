<?php

namespace Tests\Unit;

use App\Services\BedsReportDeliveryService;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BedsReportDeliveryServiceTest extends TestCase
{
    private $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_it_attaches_reports_smaller_than_one_mibibyte(): void
    {
        $path = $this->makeFile(313 * 1024);
        $filename = 'Bed_Allocations_Export_2027_All_20261124_184644.xlsx';
        $email = 'manager@example.com';
        $message = \Mockery::mock();
        $message->shouldReceive('to')->once()->with($email)->andReturnSelf();
        $message->shouldReceive('subject')->once()->with('Beds report download')->andReturnSelf();
        $message->shouldReceive('attach')->once()->with($path, [
            'as' => $filename,
            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->andReturnSelf();

        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function ($body, $callback) use ($message, $filename) {
                $callback($message);

                return strpos($body, $filename) !== false;
            });

        app(BedsReportDeliveryService::class)->emailReport($path, $filename, $email);
    }

    public function test_it_emails_a_48_hour_signed_link_at_the_one_mibibyte_boundary(): void
    {
        $path = $this->makeFile(BedsReportDeliveryService::ATTACHMENT_LIMIT_BYTES);
        $filename = 'Bed_Allocations_Export_2027_All_20261124_184644.xlsx';
        $email = 'manager@example.com';
        $message = \Mockery::mock();
        $message->shouldReceive('to')->once()->with($email)->andReturnSelf();
        $message->shouldReceive('subject')->once()->with('Beds report download')->andReturnSelf();

        $messageBody = '';
        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function ($body, $callback) use ($message, &$messageBody) {
                $messageBody = $body;
                $callback($message);

                return true;
            });

        app(BedsReportDeliveryService::class)->emailReport($path, $filename, $email);

        preg_match('/https?:\/\/\S+/', $messageBody, $matches);
        $this->assertNotEmpty($matches);

        parse_str(parse_url($matches[0], PHP_URL_QUERY), $query);
        $this->assertSame($filename, $query['filename']);
        $this->assertSame((string) (filemtime($path) + (48 * 60 * 60)), $query['expires']);
        $this->assertArrayHasKey('signature', $query);
    }

    private function makeFile(int $size): string
    {
        $path = tempnam(sys_get_temp_dir(), 'beds-report-');
        file_put_contents($path, str_repeat('x', $size));
        $this->temporaryFiles[] = $path;

        return $path;
    }
}
