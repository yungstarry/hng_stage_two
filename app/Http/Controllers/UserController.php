<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUser($id)
    {
        $user = User::findOrFail($id);
        if (Auth::id() !== $user->userId && !$user->organisations->contains(Auth::id())) {
            return response()->json([
                'status' => 'Bad request',
                'message' => 'Access denied',
                'statusCode' => 403
            ], 403);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully',
            'data' => $user,
        ], 200);
    }

    public function getUserOrganisations()
    {
        $user = Auth::user();
        $organisations = $user->organisations;

        return response()->json([
            'status' => 'success',
            'message' => 'Organisations retrieved successfully',
            'data' => [
                'organisations' => $organisations
            ]
        ], 200);
    }

    public function addUserToOrganisation($orgId, Request $request)
    {
        $request->validate([
            'userId' => 'required|exists:users,userId',
        ]);

        $organisation = Organisation::findOrFail($orgId);
        $user = User::findOrFail($request->userId);

        $isMember = $organisation->users()
        ->wherePivot('userId', Auth::id())
        ->exists();

        if(!$isMember) {
            return response()->json([
                'status' => 'Bad request',
                'message' => 'Access denied',
                'statusCode' => 403
            ], 403); 
        }

        $organisation->users()->attach($user->userId);

        return response()->json([
            'status' => 'success',
            'message' => 'User added to organisation successfully',
        ], 200);
    }
}
