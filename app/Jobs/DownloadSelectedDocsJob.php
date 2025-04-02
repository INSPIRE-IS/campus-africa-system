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

class DownloadSelectedDocsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $year;
    protected $prop;
    protected $institution;
    protected $student_type;
    protected $lease_filter;
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
    public function __construct($year,  $prop, $institution, $student_type, $lease_filter, $email)
    {
        $this->year = $year;
        $this->prop = $prop;
        $this->institution = $institution;
        $this->student_type = $student_type;
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
            Artisan::call('download:selected_docs', ['year' => $this->year, 'prop' => $this->prop, 'institution' => $this->institution, 'student_type' => $this->student_type, 'lease_filter' => $this->lease_filter, 'email' => $this->email]);
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

        // Send email without template
        \Mail::raw($exception->getMessage(), function ($message) use ($email) {
            $message->to($email)
                ->subject('Test Email');
        });
    }
}
