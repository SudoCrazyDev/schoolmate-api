<?php

namespace App\Http\Controllers;

use App\Models\InstitutionTimeSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InstitutionTimeScheduleController extends Controller
{
    public function add_institution_time_schedule(Request $request)
    {
        try {
            InstitutionTimeSchedule::create([
                'title' => $request->title,
                'institution_id' => $request->institution_id,
                'start_working_time' => $request->start_working_time,
                'end_working_time' => $request->end_working_time,
                'early_time_in' => $request->early_time_in,
                'late_time_in' => $request->late_time_in,
                'break_in' => $request->break_in,
                'break_out' => $request->break_out,
                'valid_check_out' => $request->valid_check_out,
                'late_check_out' => $request->late_check_out,
                'color' => $request->color
            ]);
            return response()->json([
                'message' => 'Institution Time Schedule Created!'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => 'Invalid Creating Institution Time Schedule!'
            ], 400);
        }
    }

    public function update_institution_time_schedule(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:institution_time_schedules,id',
                'title' => 'sometimes|nullable',
                'start_working_time' => 'sometimes|nullable',
                'end_working_time' => 'sometimes|nullable',
                'early_time_in' => 'sometimes|nullable',
                'late_time_in' => 'sometimes|nullable',
                'break_in' => 'sometimes|nullable',
                'break_out' => 'sometimes|nullable',
                'valid_check_out' => 'sometimes|nullable',
                'late_check_out' => 'sometimes|nullable',
                'color' => 'sometimes|nullable',
            ]);
            if($validator->failed()){
                return response()->json([
                    'message' => $validator->errors()
                ], 400);
            }
            $validated = $validator->validated();
            DB::transaction(function() use($validated, $request){
                InstitutionTimeSchedule::where('id', $request->id)->first()->update($validated);
            });
            return response()->json([
                'message' => 'Institution Time Schedule Updated!'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => 'Invalid Updating Institution Time Schedule!'
            ], 400);
        }
    }

    public function delete_institution_time_schedule($time_schedule_id)
    {
        try {
            DB::transaction(function() use($time_schedule_id){
                InstitutionTimeSchedule::destroy($time_schedule_id);
            });
            return response()->json([
                'message' => 'Institution Time Schedule Deleted!'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => 'Invalid Deleting Institution Time Schedule!'
            ], 400);
        }
    }

    public function get_institution_time_schedules($institution_id)
    {
        $time_schedules = InstitutionTimeSchedule::where('institution_id', $institution_id)->get();
        return response()->json([
            'data' => $time_schedules,
        ], 200);
    }
}
