<?php

namespace App\Http\Controllers;

use App\Models\InstitutionSection;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\StudentGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SectionController extends Controller
{
    public function get_all_sections($institution_id)
    {
        return InstitutionSection::where('institution_id', $institution_id)->with(['class_adviser', 'subjects'])->get();
    }
    
    public function create_section(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_adviser' => 'required|exists:users,id',
            'institution_id' => 'required|exists:institutions,id',
            'grade_level' => 'required|string',
            'title' => 'required',
            'academic_year' => 'nullable'
        ]);
        if($validator->failed()){
            return response()->json([
                'message' => $validator->errors()
            ], 400);
        }
        $validated = $validator->validated();
        try {
            DB::transaction(function() use ($validated){
                InstitutionSection::create($validated);
            });
            return response()->json([
                'data' => $this->get_all_sections($request->institution_id),
                'message' => "Section Created!"
            ], 201);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'message' => "Error Creating Section"
            ], 400);
        }
    }
    
    public function creaet_section_with_subject(Request $request)
    {
        try {
            DB::transaction(function() use($request){
                $section = InstitutionSection::create([
                    'class_adviser' => $request->class_adviser,
                    'institution_id' => $request->institution_id,
                    'grade_level' => $request->grade_level,
                    'title' => $request->title,
                    'academic_year' => $request->academic_year
                ]);
                if(count($request->subjects) > 0){
                    foreach ($request->subjects as $subject) {
                        SectionSubject::insert([...$subject, 'section_id' => $section->id]);
                    }
                }
            });
            return response()->json([
                'message' => 'Section Created!'
            ], 201);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'message' => 'Failed to create section'
            ], 400);
        }
        return $request->all();
    }
    
    public function update_section(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'section_id' => 'required|exists:institution_sections,id',
            'class_adviser' => 'sometimes|exists:users,id',
            'institution_id' => 'sometimes|exists:institutions,id',
            'grade_level' => 'sometimes|string',
            'title' => 'sometimes',
            'academic_year' => 'sometimes|nullable'
        ]);
        if($validator->failed()){
            return response()->json([
                'message' => $validator->errors()
            ], 400);
        }
        $validated = $validator->validated();
        try {
            DB::transaction(function() use ($validated, $request){
                InstitutionSection::where('id', $request->section_id)->first()->update($validated);
            });
            return response()->json([
                'data' => $this->get_all_sections($request->institution_id),
                'message' => "Section Updated!"
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => "Section Update Failed!"
            ], 400);
        }
    }
    
    public function get_by_user($user_id)
    {
        return InstitutionSection::where('class_adviser', $user_id)->get();
    }
    
    public function get_section_details($section_id)
    {
        return InstitutionSection::where('id', $section_id)->with(
        'subjects.subject_teacher',
        'students.grades.subject.subject_teacher',
        'students.values',
        'students.attendance',
        'class_adviser',
        'institution.principal',
        'institution.school_days'
        )->first();
    }
    
    public function delete_section($section_id)
    {
        try {
            DB::transaction(function() use ($section_id){
                $subjects = SectionSubject::where('section_id', $section_id)->get();
                foreach($subjects as $subject){
                    StudentGrade::where('subject_id', $subject->id)->delete();
                    SectionSubject::find($subject->id)->delete();
                }
                DB::table('student_sections')->where('section_id', $section_id)->delete();
                InstitutionSection::find($section_id)->delete();
            });
            return response()->json([
                'message' => 'Section Deleted!'
            ], 200);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'message' => 'Failed to delete section'
            ], 400);
        }
    }
    
    public function get_sections_subjects_with_grades($institution_id){
        return InstitutionSection::where('institution_id', $institution_id)->with(['subjects.student_grades'])->get();
    }
}
