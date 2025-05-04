<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaResource;
use App\Http\Resources\user\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{

    public function authenticate(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422); // Unprocessable Entity
        }

        // Attempt authentication using the validated credentials
        if (!Auth::attempt($validator->validated())) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password'
            ], 401); // Unauthorized
        }

        /** @var \App\Models\User $user */ // Type hint for better autocompletion
        $user = Auth::user();

        if ($user->status === 'banned') {
            Auth::logout(); // Log out the banned user
            return response()->json([
                'status' => false,
                'message' => 'Your account is banned'
            ], 403); // Forbidden
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'token' => $token,
            'user' => new UserResource($user)
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
