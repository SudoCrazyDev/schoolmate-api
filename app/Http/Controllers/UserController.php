<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserEmploymentDetail;
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
            return User::where('id', $user->id)->with([
                'roles:title,slug',
                'institutions:id,title,logo,address',
                'institutions.subscriptions.subscription'
                ])->first();
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
            })->with('roles:id,title,slug', 'employment')->get();
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
            })->with('roles:title,slug', 'loads', 'advisory')->get();
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
    
    public function update_user(Request $request, $user_id)
    {
        try {
            DB::transaction(function() use($request, $user_id) {
                User::where('id', $user_id)
                ->update([
                    'first_name' => $request->first_name,
                    'middle_name' => $request->middle_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email
                ]);
            });
            return response()->json([
                'message' => 'User Updated!'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to update user!'
            ], 200);
        }
    }
    
    public function validate_email($email)
    {
        $user = User::where('email', $email)->first();
        if($user){
            return response()->json([
                'message' => 'Email exists'
            ], 400);
        }else{
            return response()->json([
                'message' => 'Email is Valid'
            ], 200);
        }
    }
    
    public function update_user_role(Request $request, $user_id)
    {
        try {
            DB::transaction(function() use($request, $user_id){
                DB::table('user_roles')->where('user_id', $user_id)->update(['role_id' => $request->roles]);
            });
            return response()->json([
                'message' => 'User Role Updated!'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to update user role!'
            ], 400);
        }
    }
    
    public function update_user_password(Request $request)
    {
        try {
            $user = User::findOrFail($request->user_id);
            $user->password = $request->password;
            $user->is_new = 0;
            $user->save();
            return response()->json([
                'message' => 'Password Updated!'
            ], 201);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'message' => 'Failed to update!'
            ], 400);
        }
        
    }
    
    public function get_users_with_attendance_logs(Request $request)
    {
        try {
            try {
                $users = User::whereHas('institutions', function($query) use($request){
                   $query->where('institutions.id', $request->institution_id);
                })->with(
                    [
                    'roles:title,slug',
                    'institutions',
                    'institutions.principal',
                    'employment',
                    'custom_attendances' => function($query) use($request){
                        $query->whereBetween('auth_date', [$request->start_date, $request->end_date]);
                    },
                    'proper_attendances' => function($query) use($request){
                        $query->whereBetween('auth_date', [$request->start_date, $request->end_date]);
                    }]
                )->get();
                return response()->json([
                    'data' => $users
                ], 200);
            } catch (\Throwable $th) {
                Log::info($th);
                return response()->json([
                    'message' => "Error Fetching Users"
                ], 400);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    
    public function update_user_employment_details(Request $request, $user_id)
    {
        try {
            UserEmploymentDetail::updateOrCreate(
                ['user_id' => $user_id],
                [
                    'employee_id' => $request->employee_id,
                    'date_started' => $request->date_started,
                    'position' => $request->position
                ]
            );
            return response()->json([
                'message' => 'User Employment Details Updated!'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to update user employment details!'
            ], 400);
        }
    }
}
