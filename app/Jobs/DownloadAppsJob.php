<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class DownloadAppsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $year;
    protected $userId;
    protected $email;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 900;

    /**
     * The maximum number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($year, $userId, $email)
    {
        $this->year = $year;
        $this->userId = $userId;
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Artisan::call('download:apps', [
                'year' => $this->year,
                'user_id' => $this->userId,
                'email' => $this->email,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        $email = $this->email;

        \Mail::raw($exception->getMessage(), function ($message) use ($email) {
            $message->to($email)
                ->subject('Applications download failed');
        });
    }
}
