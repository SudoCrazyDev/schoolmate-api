<?php

namespace App\Jobs;

use App\Models\TeacherAttendance;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessTeachersAttendanceCSV implements ShouldQueue
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
        $rows = file(storage_path("app/private/{$this->path}"), FILE_SKIP_EMPTY_LINES);

        $rows = array_slice($rows, 1);

        foreach($rows as $row){
            $row = ltrim($row, "'");
            $columns = str_getcsv($row);
            if (count($columns) < 5) continue;
            $date_time = Carbon::parse($columns[3]);
            TeacherAttendance::firstOrCreate([
                'institution_id' =>  $this->institution_id,
                'employee_id' => $columns[0],
                'status' => $columns[4],
                'auth_date' => $date_time->toDateString()
            ],
            [
                'institution_id' =>  $this->institution_id,
                'employee_id' => $columns[0],
                'status' => $columns[4],
                'date_time' => $columns[3],
                'auth_date' => $date_time->toDateString(),
                'auth_time' => $date_time->toTimeString()
            ]);
        }
        Storage::disk('local')->delete($this->path);
    }
}
