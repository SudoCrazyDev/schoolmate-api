<?php

namespace App\Http\Controllers;

use App\Models\InstitutionGradingAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MetaController extends Controller
{
    public function get_grades_access($institution_id)
    {
        $grading_access = InstitutionGradingAccess::where('institution_id', $institution_id)->get();
        if(count($grading_access) === 0){
            $access = InstitutionGradingAccess::create([
                'institution_id' => $institution_id,
                'quarter_one' => 0,
                'quarter_two' => 0,
                'quarter_three' => 0,
                'quarter_four' => 0,
            ]);
            return response()->json([
                'data' => $access
            ], 201);
        }else{
            return response()->json([
                'data' => $grading_access
            ], 200);
        }
    }
    
    public function update_grading_access(Request $request, $institution_id)
    {
        $grading_access = InstitutionGradingAccess::where('institution_id', $institution_id)->first();
        if($grading_access){
            $grading_access->quarter_one = $request->quarter_one;
            $grading_access->quarter_two = $request->quarter_two;
            $grading_access->quarter_three = $request->quarter_three;
            $grading_access->quarter_four = $request->quarter_four;
            $grading_access->save();
            return response()->json([
                'message' => 'Success'
            ], 200);
        }
    }
}
