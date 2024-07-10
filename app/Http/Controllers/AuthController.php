<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Bad request',
                'message' => 'Registration unsuccessful',
                'statusCode' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $user = User::create([
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            $organisation = Organisation::create([
                'name' => $request->firstName . "'s Organisation",
                'description' => '', // Default to empty string if not provided
            ]);

            $organisation->users()->attach($user->userId);
            $token = $user->createToken('main')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful',
                'data' => [
                    'accessToken' => $token,
                    'user' => $user,
                ]
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return response([
                'status' => 'Bad request',
                'message' => 'Registration unsuccessful',
                'statusCode' => 400
            ], 400);
        }
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember') ? $request->remember : false;

        if (!Auth::attempt($credentials, $remember)) {
            return response()->json([
                'status' => 'Bad request',
                'message' => 'Authentication failed',
                'statusCode' => 401,
            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken('main')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'accessToken' => $token,
                'user' => $user,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ], 200);
    }
}
