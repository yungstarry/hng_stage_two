<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $data = $request;

        try {
            $user = User::create([
                "firstName" => $data["firstName"],
                "lastName" => $data["lastName"],
                "email" => $data["email"],
                "password" => bcrypt($data["password"]),
            ]);


            $organisation = Organisation::create([
                'name' => $data['firstName'] . "'s Organisation",
            ]);

            $organisation->users()->attach($user->userId);
            $token = $user->createToken("main")->plainTextToken;

            return response()->json([
                'status' => "success",
                'message' => "Registration successful",
                "data" => [
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

    public function login(LoginRequest $request,)
    {
        $data = $request->validated();
        $remember = $data['remember'] ?? false;
        unset($data['remember']);


        if (!Auth::attempt($data, $remember)) {
            return response([
                'status' => 'Bad request',
                'message' => 'Authentication failed',
                'statusCode' => 401

            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken('main')->plainTextToken;

        return response([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'accessToken' => $token,
                'user' => $user,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {

            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
