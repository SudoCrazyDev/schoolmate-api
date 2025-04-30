<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCustomTeachersAttendanceXLS implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;
    
    public string $path;
    public string $institution_id;

    /**
     * Create a new job instance.
     */
    public function __construct(string $path, string $institution_id)
    {
        $this->path = $path;
        $this->institution_id = $institution_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
