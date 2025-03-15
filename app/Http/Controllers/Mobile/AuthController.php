<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request){

        if(Auth::guard('mobile')->attempt($request->only('username', 'password'))){
            $user = User::where('username', $request->username)->first();
            return response()->json([
                'success' => true,
                'message' => 'login success',
                'data' => [
                    'token' => $user->createToken('mobile')->plainTextToken,
                    'user' => [
                        'name' => $user->name,
                        'username' => $user->username,
                        'roles' => $user->roles,
                    ]
                ]
            ]);
        }

        return response()->json([
            'username atau password salah'
        ], 401);
    }
}
