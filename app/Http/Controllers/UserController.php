<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if(!$user){
            return response()->json([
                'data' => null,
                'message' => 'Invalid Credentials'
            ], 400);
        };
        if(Hash::check($request->password, $user->password)){
            $user->token = Str::random(25);
            $user->save();
            return User::where('id', $user->id)->with(['roles:title,slug', 'institutions:id,title,logo'])->first();
        } else {
            return response()->json([
                'data' => null,
                'message' => 'Invalid Credentials'
            ], 401);
        }
    }

    public function get_users_by_role($slug)
    {
        try {
            Log::info($slug);
            $users = User::whereHas('roles', function ($query) use ($slug){
                $query->where('roles.slug', $slug);
            })->paginate(10);
            return response()->json([
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => null,
                'message' => 'Error Fetching Users'
            ], 400);
        }
    }

    public function create_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'middle_name' => 'nullable',
            'last_name' => 'required',
            'ext_name' => 'nullable',
            'gender' => 'nullable',
            'birthdate' => 'nullable',
            'email' => 'required|unique:users'
        ]);
        if($validator->fails()){
            return response()->json([
                'message' => $validator->errors()
            ], 400);
        }
        $validated = $validator->validated();
        try {
            DB::transaction(function() use ($validated, $request){
                $user = User::create([...$validated, 'password' => 'password']);
                DB::table('user_institutions')->insert([
                    'user_id' => $user->id,
                    'institution_id' => $request->institution_id
                ]);
                DB::table('user_roles')->insert([
                    'user_id' => $user->id,
                    'institution_id' => $request->institution_id,
                    'role_id' => $request->role_id
                ]);
            });
            return response()->json([
                'data' => $this->get_users_by_institutions($request->institution_id),
                'message' => "User Created!"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => "Error Adding User"
            ], 400);
        }
    }

    public function get_users_by_institutions($institution_id)
    {
        try {
            $users = User::whereHas('institutions', function($query) use($institution_id){
               $query->where('institutions.id', $institution_id);
            })->with('roles:title,slug')->paginate(10);
            return response()->json([
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'message' => "Error Fetching Users"
            ], 400);
        }
    }
    
    public function get_all_users($institution_id)
    {
        try {
            $users = User::whereHas('institutions', function($query) use($institution_id){
               $query->where('institutions.id', $institution_id);
            })->with('roles:title,slug', 'loads', 'advisory')->paginate(10);
            return response()->json([
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'message' => "Error Fetching Users"
            ], 400);
        }
    }
}
