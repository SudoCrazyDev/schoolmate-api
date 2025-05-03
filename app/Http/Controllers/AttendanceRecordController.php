<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTeachersAttendanceCSV;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
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
    
    public function custom_upload_teacher_attendance_log(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file',
            ]);

            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $grouped = [];
            $unknownEntries = [];

            foreach ($rows as $index => $row) {

                if ($index === 0) continue;

                $fullName = trim($row[0]);
                if (!str_contains($fullName, ',')) continue;

                [$lastName, $rest] = explode(',', $fullName, 2);
                $lastName = trim($lastName);
                $restParts = explode(' ', trim($rest));

                $restParts = array_filter($restParts, function ($part) {
                    return !preg_match('/^[A-Z]\.$/i', $part);
                });

                $firstName = implode(' ', $restParts);

                $userQuery = User::where('first_name', 'LIKE', $firstName)
                                 ->where('last_name', 'LIKE', $lastName);

                $user = $userQuery->first();

                if (!$user) {
                    $unknownEntries[] = $fullName;
                    continue;
                }

                $dateTimeStr = trim($row[2]);

                try {
                    $carbonDateTime = Carbon::createFromFormat('d/m/Y h:i:sa', $dateTimeStr);
                } catch (\Exception $e) {
                    continue;
                }

                $date = $carbonDateTime->toDateString();

                $grouped[$user->id][$date][] = [
                    'datetime' => $carbonDateTime,
                    'employee' => $fullName,
                ];
            }

            foreach ($grouped as $userId => $dates) {

                foreach ($dates as $date => $entries) {

                    $statuses = ['check-in', 'break-out', 'break-in', 'check-out'];

                    foreach ($entries as $i => $entry) {
                        $status = $statuses[$i] ?? 'unknown-entry';
                        TeacherAttendance::firstOrCreate(
                        [
                            'institution_id' =>  $request->institution_id,
                            'employee_id' => $userId,
                            'status'      => $status,
                            'auth_date' => $entry['datetime']->toDateString()
                        ],
                        [
                            'institution_id' => $request->institution_id,
                            'employee_id' => $userId,
                            'employee'    => $entry['employee'],
                            'status'      => $status,
                            'date_time'   => $entry['datetime']->format('Y-m-d H:i:s'),
                            'auth_date'   => $entry['datetime']->toDateString(),
                            'auth_time'   => $entry['datetime']->toTimeString(),
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $unknownEntries,
                'message' => 'XLS uploaded successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to upload XLS',
            ], 400);
        }
    }
    
    public function update_attendance_record(Request $request)
    {
        try {
            $request->validate([
                'val' => 'required',
                'id' => 'required',
            ]);
            $attendanceRecord = TeacherAttendance::find($request->id);

            if (!$attendanceRecord) {
                return response()->json([
                    'message' => 'Attendance record not found',
                ], 404);
            }

            $attendanceRecord->update(['auth_time' => $request->val]);

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance record updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to update attendance record',
            ], 400);
        }
    }
}
