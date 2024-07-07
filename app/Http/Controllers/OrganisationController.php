<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Http\Requests\StoreOrganisationRequest;
use App\Http\Requests\UpdateOrganisationRequest;
use Illuminate\Support\Facades\Auth;

class OrganisationController extends Controller
{
    public function getOrganisation($orgId)
    {
        $organisation = Organisation::findOrFail($orgId);

        if (!$organisation->users->contains(Auth::id())) {
            return response()->json([
                'status' => 'Bad request',
                'message' => 'Access denied',
                'statusCode' => 403
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Organisation retrieved successfully',
            'data' => $organisation,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrganisationRequest $request)
    {
        $data =$request->validated();

        $organisation = Organisation::create([
            'name'=> $data['name'],
            'description'=> $data['description'],
        ]);

        $organisation->users()->attach(Auth::id());

        return response()->json([
            'status' => 'success',
            'message' => 'Organisation created successfully',
            'data' => $organisation
        ], 201);
    }
  
}
