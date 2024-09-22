<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentGrade;
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
            Log::info($th);
            return response()->json([
                'message' => 'Failed to create Student'
            ], 400);
        }
    }
    
    public function update_student(Request $request)
    {
        
    }
    
    public function submit_grade(Request $request)
    {
        try {
            StudentGrade::insert($request->grades);
            return response()->json([
                'message' => 'Student Grades Submitted!'
            ], 201);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'message' => 'Failed to Submit Grades!'
            ], 400);
        }
    }
}
