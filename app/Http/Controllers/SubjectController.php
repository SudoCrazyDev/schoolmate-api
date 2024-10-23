<?php

namespace App\Http\Controllers;

use App\Models\InstitutionSection;
use App\Models\SectionSubject;
use App\Models\StudentGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use function PHPSTORM_META\map;

class SubjectController extends Controller
{
    public function get_subjects_by_section($section_id)
    {
        return SectionSubject::where('section_id', $section_id)->with('subject_teacher')->get();
    }
    
    public function get_subjects_by_user($user_id)
    {
        return SectionSubject::where('subject_teacher', $user_id)->with('section')->get();
    }
    
    public function create_subject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_teacher' => 'nullable|exists:users,id',
            'section_id' => 'exists:institution_sections,id',
            'start_time' => 'required',
            'end_time' => 'required',
            'title' => 'required',
        ]);
        if($validator->failed()){
            return response()->json([
                'message' => $validator->errors()
            ], 400);
        }
        $validated = $validator->validated();
        try {
            DB::transaction(function() use ($validated){
                SectionSubject::create($validated);
            });
            return response()->json([
                'data' => $this->get_subjects_by_section($validated['section_id']),
                'message' => "Subject Created!"
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => "Failed to create Subject"
            ], 400);
        }
    }
    
    public function update_subject($subject_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_teacher' => 'nullable|exists:users,id',
            'section_id' => 'exists:institution_sections,id',
            'start_time' => 'required',
            'end_time' => 'required',
            'title' => 'required',
        ]);
        if($validator->failed()){
            return response()->json([
                'message' => $validator->errors()
            ], 400);
        }
        $validated = $validator->validated();
        try {
            DB::transaction(function() use ($validated, $subject_id){
                SectionSubject::where('id', $subject_id)->first()->update($validated);
            });
            return response()->json([
                'data' => $this->get_subjects_by_section($validated['section_id']),
                'message' => "Subject Updated!"
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => "Subject Update Failed!"
            ], 400);
        }
    }
    
    public function check_for_teacher_conflict(Request $request)
    {
        return SectionSubject::where(['subject_teacher' => $request->subject_teacher, 'start_time' => $request->start_time])->with('section')->get();
    }
    
    public function delete_subject($subject_id)
    {
        try {
            DB::transaction(function() use($subject_id){
                $subject = SectionSubject::find($subject_id);
                $subject->delete();
            });
            return response()->json([
                'message' => 'Subject Deleted!'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to delete subject'
            ], 400);
        }
    }
    
    public function get_subject_details($subject_id)
    {
        try {
            DB::transaction(function() use($subject_id){
                $genMapeh = [
                    'music',
                    'arts',
                    'pe',
                    'health'
                ];
                $g7Mapeh = [
                    'music & arts',
                    'pe & health'
                ];
                $subject = SectionSubject::with('section')->where('id', $subject_id)->first();
                if(strtolower($subject->title) === 'mapeh'){
                    $sub_subjects = SectionSubject::where('parent_subject', $subject_id)->get();
                    if(count($sub_subjects) === 0){
                        if($subject->section->grade_level == '7'){
                            foreach($g7Mapeh as $sub){
                                SectionSubject::create([
                                    'subject_teacher' => $subject->subject_teacher,
                                    'parent_subject' => $subject->id,
                                    'section_id' => $subject->section_id,
                                    'sem' => $subject->sem,
                                    'title' => $sub,
                                    'start_time' => $subject->start_time,
                                    'end_time' => $subject->end_time
                                ]);
                            }
                        } else {
                            foreach($genMapeh as $sub){
                                SectionSubject::create([
                                    'subject_teacher' => $subject->subject_teacher,
                                    'parent_subject' => $subject->id,
                                    'section_id' => $subject->section_id,
                                    'sem' => $subject->sem,
                                    'title' => $sub,
                                    'start_time' => $subject->start_time,
                                    'end_time' => $subject->end_time
                                ]);
                            }
                        }
                    }
                }
            });
            $subject = SectionSubject::with('sub_subjects')->where('id', $subject_id)->first();
            return SectionSubject::where('id', $subject_id)->with(['students.grades' => function($query) use ($subject_id, $subject){
                if(strtolower($subject->title) === 'mapeh'){
                    $query->whereIn('subject_id', $subject->sub_subjects->pluck('id'));
                }else{
                    $query->where('subject_id', $subject_id);
                }
                
            }, 'sub_subjects'])->first();
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'message' => 'Failed to Fetch Subject Details'
            ], 400);
        }
    }
    
    public function unlock_subject_grades(Request $request, $subject_id)
    {
        try {
            DB::transaction(function() use ($request, $subject_id){
                StudentGrade::where('subject_id', $subject_id)
                ->where('quarter', $request->quarter)
                ->update(['is_locked' => $request->is_locked]);
            });
            return response()->json([
                'message' => 'Subject Unlocked!'
            ], 200);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'message' => 'Failed to update subject'
            ], 400);
        }
        
    }
    
    public function get_subjects_missing_grades($section_id)
    {
        try {
            $section = InstitutionSection::with('subjects.teacher')->where('id', $section_id)->first();
            $subject_stats = [];
            foreach($section->subjects ?? [] as $subject){
                $count = StudentGrade::where(['subject_id' => $subject->id, 'quarter' => 1])->count();
                if(strtolower($subject->title) !== 'mapeh'){
                    array_push($subject_stats, ['title' => $subject->title, 'subject_teacher' => $subject->teacher, 'graded' => $count]);
                }
            }
            return response()->json([
                'data' => $subject_stats
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
            ], 400);
        }
    }
}
