<?php

namespace App\Http\Controllers;

use App\Models\SectionSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
        return SectionSubject::where('id', $subject_id)->with(['students.grades' => function($query) use ($subject_id){
            $query->where('subject_id', $subject_id);
        }])->first();
    }
}
