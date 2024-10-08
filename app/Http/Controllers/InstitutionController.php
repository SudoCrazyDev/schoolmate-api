<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InstitutionController extends Controller
{
    public function get_all_institution()
    {
        return Institution::paginate(10);
    }

    public function create_institution(Request $request){
        $validated = $request->validate([
            'title' => 'required',
            'abbr' => 'nullable'
        ]);
        if(!$validated){
            return response()->json([
                'data' => null,
                'message' => 'Invalid Creating Institution!'
            ], 400);
        }
        try {
            Institution::create($validated);
            return response()->json([
                'data' => $this->get_all_institution(),
                'message' => 'Institution Added'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => 'Error on Creating Institution'
            ], 400);
        }
    }

    public function update_institution(Request $request)
    {
        $file = $request->file('logo');
        $path = $request->logo;
        if($file){
            $path = Storage::put($request->id . "/logo", $file, 'public');
        }
        try {
            DB::transaction(function() use($request, $path){
                Institution::where('id', $request->id)
                ->update([
                    'title' => $request->institution,
                    'abbr' => $request->abbr,
                    'address' => $request->address,
                    'logo' => $path
                ]);
            });
            return response()->json([
                'data' => $this->get_all_institution(),
                'message' => 'Institution Updated!'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => 'Error on Updating Institution'
            ], 400);
        }
    }
}
