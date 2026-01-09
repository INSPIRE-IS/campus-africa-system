<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DownloadCompatibilityDocs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $year;
    protected $building;
    protected $email;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

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
    public function __construct($year, $email, $building)
    {
        $this->year = $year;
        $this->building = $building;
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

            if ($this->building) {
                Log::info("Downloading compatibility docs for {$this->year} and {$this->building}");
                Artisan::call('download:compatibility_docs', ['year' => $this->year, 'email' => $this->email, '--building' => $this->building]);
            } else {
                Log::info("Downloading compatibility docs for {$this->year} and all buildings");
                Artisan::call('download:compatibility_docs', ['year' => $this->year, 'email' => $this->email]);
            }
        } catch (\Exception $e) {
            // You can perform some error handling here if necessary
            Log::error('error on job ');
            Log::error($e->getMessage());
            // Rethrow the exception so that the job fails and the `failed` method is called
            // throw $e;
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
        //Email result to user
        $email = $this->email;

        // Send email without template
        \Mail::raw($exception->getMessage(), function ($message) use ($email) {
            $message->to($email)
                ->subject('Test Email');
        });
    }
}
