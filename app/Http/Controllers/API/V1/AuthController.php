<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponser;

class AuthController extends Controller
{
    use ApiResponser;
    //? Register User
    public function register(RegisterRequest $request)
    {
        if (User::where('email', $request->email)->exists()) {
            // return response()->json([
            //     'message' => __('messages.user_exists', ['email' => $request->email]),
            // ], 422);
            return $this->errorResponse(__('messages.user_exists', ['email' => $request->email]), 422);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        // return response()->json([
        //     'message' => __('messages.register_success'),
        //     'user' => $user,
        //     'token' => $token,
        // ], 201);

        return $this->successResponse(__('messages.register_success'), [
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    //? Login User
    public function login(LoginRequest $request)
    {

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'status' => '0'
            ], 404);
        }

        if (Hash::check($request->password, $user->password) === false) {
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

        // return response()->json(['message' => 'Logged out successfully']);
        return $this->successResponse(__('messages.logout_success'));
    }
}
