<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentCoreValue;
use App\Models\StudentGrade;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function create_student(Request $request)
    {
        $validator = Validator::make($request->basic_information, [
            'institution_id' => 'exists:institutions,id',
            'lrn' => 'nullable',
            'first_name' => 'required',
            'middle_name' => 'nullable',
            'last_name' => 'required',
            'ext_name' => 'nullable',
            'gender' => 'nullable',
            'birthdate' => 'nullable'
        ]);
        if($validator->fails()){
            return response()->json([
                'message' => 'Validation Failed'
            ], 400);
        }
        $validated = $validator->validated();
        try {
            DB::transaction(function() use ($validated, $request){
                $student = Student::create($validated);
                DB::table('student_sections')->insert([
                    'student_id' => $student->id,
                    'section_id' => $request->section
                ]);
            });
            return response()->json([
                'message' => 'Student Created!'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to create Student'
            ], 400);
        }
    }
    
    public function update_student(Request $request, $student_id)
    {
        $validator = Validator::make($request->basic_information, [
            'lrn' => 'nullable',
            'first_name' => 'required',
            'middle_name' => 'nullable',
            'last_name' => 'required',
            'ext_name' => 'nullable',
            'gender' => 'nullable',
            'birthdate' => 'nullable'
        ]);
        if($validator->fails()){
            return response()->json([
                'message' => 'Validation Failed'
            ], 400);
        }
        $validated = $validator->validated();
        try {
            DB::transaction(function() use ($validated, $student_id){
                Student::findOrFail($student_id)->update($validated);
            });
            return response()->json([
                'message' => 'Student Updaetd!'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to update Student'
            ], 400);
        }
    }
    
    public function submit_grade(Request $request)
    {
        try {
            foreach($request->grades as $grade){
                StudentGrade::updateOrCreate(
                    ['student_id' => $grade['student_id'], 'subject_id' => $grade['subject_id'], 'quarter' => $grade['quarter']],
                    ['grade' => $grade['grade'], 'is_locked' => 1]
                );
            }
            return response()->json([
                'message' => 'Student Grades Submitted!'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to Submit Grades!'
            ], 400);
        }
    }
    
    public function unlock_student_grade(Request $request, $grade_id)
    {
        try {
            $grade = StudentGrade::find($grade_id);
            $grade->is_locked = $request->state;
            $grade->save();
            return response()->json([
                'message' => 'Grade Updated!'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error updating Grade!'
            ], 200);
        }
        
    }
    
    public function count_students_per_section($section_id)
    {
        try {
            $students = Student::whereHas('sections', function($query) use($section_id){
                $query->where('section_id', $section_id);
            })->get();
            return response()->json([
                'data' => $students,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to retrieve students.'
            ], 400);
        }
    }
    
    public function count_students_per_institution($institution_id)
    {
        try {
            $students = Student::where('institution_id', $institution_id)->get();
            return response()->json([
                'data' => $students,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to retrieve students.'
            ], 400);
        }
    }
    
    public function submit_observed_values(Request $request)
    {
        try {
            foreach($request->corevalues as $corevalue){
                StudentCoreValue::updateOrCreate(
                    ['student_id' => $corevalue['student_id'], 'academic_year' => $corevalue['academic_year'], 'quarter' => $corevalue['quarter'], 'core_value' => $corevalue['core_value']],
                    ['remarks' => $corevalue['remarks']]
                );
            }
            return response()->json([
                'message' => 'Student Core Values Submitted!'
            ], 201);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'message' => 'Failed to Submit Core Values!'
            ], 400);
        }
    }
    
    public function get_student_info($student_id)
    {
        try {
            $student = Student::findOrFail($student_id);
            return response()->json([
                'data' => $student
            ], 200);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'mesage' => 'Failed to fetch student'
            ], 400);
        }
    }
}
