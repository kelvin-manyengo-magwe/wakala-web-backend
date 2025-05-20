<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;



class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
          $user = Auth::user();


        $token = $user->createToken('mobile-app-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,

        ]);
    }
}
