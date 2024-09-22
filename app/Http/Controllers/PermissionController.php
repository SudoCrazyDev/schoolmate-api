<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    public function get_all_permissions()
    {
        return Permission::paginate(10);
    }
    
    public function create_permission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string',
            'slug' => 'string|unique:permissions,slug'
        ]);
        if($validator->fails()){
            return response()->json([
                'data' => null,
                'message' => 'Invalid Creating Permission!'
            ], 400);
        }
        $validated = $validator->validated();
        try {
            Permission::create($validated);
            return response()->json([
                'data' => $this->get_all_permissions(),
                'message' => 'Permission Created!'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error Creating Permission!'
            ], 400);
        }
    }
}
