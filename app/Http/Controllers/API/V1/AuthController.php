<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    //? Register User
    public function register(RegisterRequest $request)
    {

        $data = $request->validated();

        // Check if user already exists
        if (User::where('email', $data['email'])->exists()) {
            return response()->json([
                'message' => __('messages.user_exists', ['email' => $data['email']]),
            ], 422);
        }

        $user = new User($data);
        $user->password = Hash::make($data['password']);

        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => __('messages.register_success'),
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    //? Login User
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        if (Hash::check($data['password'], $user->password) === false) {
            return response()->json([
                    'message' => __('messages.invalid_credentials'),
                ], 422);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => __('messages.login_success'),
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    //? Logout User
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
