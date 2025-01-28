<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Log;
use Hash;
class AuthController extends Controller
{
    //
    public function login(Request $request){
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('phone', $request->phone)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::info('Invalid login credentials, '.$request->phone);
            return response()->json([
                'message' => 'Invalid login credentials.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        Log::info('User logged in, '.$user->phone);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        Log::info('User logged out, '.$user->phone);
        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
