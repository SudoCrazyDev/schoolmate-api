<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTeachersAttendanceCSV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceRecordController extends Controller
{
    public function upload_teacher_attendance_log(Request $request)
    {
        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt',
            ]);
            $path = $request->file('csv_file')->store('teacher_attendance_csv');
            ProcessTeachersAttendanceCSV::dispatch($path,$request->institution_id);
            return response()->json([
                'status' => 'success',
                'message' => 'CSV uploaded successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to upload CSV',
            ], 400);
        }
    }
}
