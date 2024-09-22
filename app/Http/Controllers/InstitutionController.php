<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'id' => 'required|exists:institutions,id',
            'title' => 'sometimes|nullable',
            'abbr' => 'sometimes|nullable'
        ]);
        if(!$validated){
            return response()->json([
                'data' => null,
                'message' => 'Invalid Updating Institution!'
            ], 400);
        }
        try {
            Institution::where('id', $validated['id'])->update($validated);
            return response()->json([
                'data' => $this->get_all_institution(),
                'message' => 'Institution Added'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => 'Error on Updating Institution'
            ], 400);
        }
        
    }
}
