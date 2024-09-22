<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function get_all_roles()
    {
        return Role::whereNot('slug', 'app-admin')->paginate(10);
    }
    
    public function create_role(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string',
            'slug' => 'string|unique:roles,slug'
        ]);
        if($validator->fails()){
            return response()->json([
                'message' => 'Error on Creating Role'
            ], 400);
        }
        $validated = $validator->validated();
        try {
            Role::create($validated);
            return response()->json([
                'data' => $this->get_all_roles(),
                'message' => 'Role Created!'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to Create Role'
            ], 400);
        }
    }
}
