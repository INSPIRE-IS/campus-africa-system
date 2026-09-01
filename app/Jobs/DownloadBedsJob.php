<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class DownloadBedsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $year;
    protected $property;
    protected $userId;
    protected $email;

    public $timeout = 900;
    public $tries = 1;

    public function __construct($year, $property, $userId, $email)
    {
        $this->year = $year;
        $this->property = $property;
        $this->userId = $userId;
        $this->email = $email;
    }

    public function handle()
    {
        try {
            Artisan::call('download:beds', [
                'year' => $this->year,
                'property' => $this->property,
                'user_id' => $this->userId,
                'email' => $this->email,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function failed(Exception $exception)
    {
        $email = $this->email;

        \Mail::raw($exception->getMessage(), function ($message) use ($email) {
            $message->to($email)
                ->subject('Beds report download failed');
        });
    }
}
