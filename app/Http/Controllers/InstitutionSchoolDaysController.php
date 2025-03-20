<?php

namespace App\Http\Controllers;

use App\Models\InstitutionSchoolDay;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InstitutionSchoolDaysController extends Controller
{
    public function add_school_days(Request $request)
    {
        $school_days = InstitutionSchoolDay::where(['institution_id' => $request->institution_id, 'academic_year' => $request->academic_year])->get();
        if(count($school_days) > 0) return response()->json(['message' => 'Academic Year Exists!'], 400);
        try {
            InstitutionSchoolDay::where('institution_id', $request->institution_id)->update([
                'is_default' => 1
            ]);
            InstitutionSchoolDay::create([
                'academic_year' => $request->academic_year,
                'institution_id' => $request->institution_id,
                'jan' => $request->jan,
                'feb' => $request->feb,
                'mar' => $request->mar,
                'apr' => $request->apr,
                'may' => $request->may,
                'jun' => $request->jun,
                'jul' => $request->jul,
                'aug' => $request->aug,
                'sep' => $request->sep,
                'oct' => $request->oct,
                'nov' => $request->nov,
                'dec' => $request->dec,
                'is_default' => 1
            ]);
            return response()->json(['message' => "Academic Year School Days Created!"], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => "Academic Year School Days was not created"], 400);
        }
    }

    public function update_school_days(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_day_id' => 'required',
            'jan' => 'sometimes',
            'feb' => 'sometimes',
            'mar' => 'sometimes',
            'apr' => 'sometimes',
            'may' => 'sometimes',
            'jun' => 'sometimes',
            'jul' => 'sometimes',
            'aug' => 'sometimes',
            'sep' => 'sometimes',
            'oct' => 'sometimes',
            'nov' => 'sometimes',
            'dec' => 'sometimes'
        ]);
        if($validator->fails()){
            return response()->json(['Invalid Data'], 400);
        }
        $validated = $validator->validated();
        $school_day = InstitutionSchoolDay::findOrFail($validated['school_day_id']);
        $school_day->update($validated);
        return response()->json(['message' => 'School Days Updated!'], 201);
    }

    public function get_institution_school_days($institution_id)
    {
        $school_days = InstitutionSchoolDay::where('institution_id', $institution_id)->get();
        return response()->json([
            'data' => $school_days,
        ], 200);
    }

    public function update_students_attendance(Request $request)
    {
        try {
            DB::transaction(function() use($request){
                $records = $request->records;
                foreach($records as $record){
                    $availableData = Arr::except($record, ['student_id', 'academic_year']);
                    Log::info($availableData);
                    StudentAttendance::updateOrCreate(
                        [
                            'student_id' => $record['student_id'],
                            'academic_year' => $record['academic_year']
                        ], $availableData);
                }
            });
            return response()->json(['message' => 'Students Attendance Updated!'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Failed to update students attendance'], 400);
        }
        
    }
}
