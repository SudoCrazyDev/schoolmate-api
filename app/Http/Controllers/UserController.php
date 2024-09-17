<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function login(Request $request){
        $user = User::where('email', $request->email)->first();
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
}
