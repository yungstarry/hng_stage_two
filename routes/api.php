<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/users/{id}', [UserController::class, 'getUser']);
    Route::get('/organisations', [UserController::class, 'getUserOrganisations']);
    Route::post('/organisations/{orgId}/users', [UserController::class, 'addUserToOrganisation']);

    Route::post('/organisations', [OrganisationController::class, 'store']);
    Route::get('/organisations/{orgId}', [OrganisationController::class, 'getOrganisation']);
});

Route::post('/auth/register', [AuthController::class, 'signup']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/run-migration', function () {
    Artisan::call('optimize:clear');
    Artisan::call('migrate:fresh --seed');
    return "Migration executed successfully";
});

