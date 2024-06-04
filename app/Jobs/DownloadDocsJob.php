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

class DownloadDocsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $year;
    protected $prop;
    protected $lease_filter;
    protected $email;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($year, $prop, $lease_filter, $email)
    {
        $this->year = $year;
        $this->prop = $prop;
        $this->lease_filter = $lease_filter;
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
            Artisan::call('download:docs', ['year' => $this->year, 'prop' => $this->prop, 'lease_filter' => $this->lease_filter, 'email' => $this->email]);
        } catch (\Exception $e) {
            // You can perform some error handling here if necessary

            // Rethrow the exception so that the job fails and the `failed` method is called
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
        //Email result to user
        $email = $this->email;
        $email = 'roger@whitehart.co.za';

        // Send email without template
        \Mail::raw('Something went wrong in job', function ($message) use ($email) {
            $message->to($email)
                ->subject('Test Email');
        });
    }
}
