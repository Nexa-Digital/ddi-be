<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request){

        if(Auth::attempt($request->only('username', 'password'))){
            $user = Admin::where('username', $request->username)->first();
            return response()->json([
                'success' => true,
                'message' => 'login success',
                'data' => [
                    'token' => $user->createToken('web')->plainTextToken,
                    'user' => [
                        'name' => $user->name,
                        'username' => $user->username,
                    ]
                ]
            ]);
        }

        return response()->json([
            'username atau password salah'
        ], 401);
    }
}
