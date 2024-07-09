<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // User Routes
    Route::get('/users/{id}', [UserController::class, 'getUser']);
    Route::get('/organisations', [UserController::class, 'getUserOrganisations']);
    Route::post('/organisations/{orgId}/users', [UserController::class, 'addUserToOrganisation']);

    // Organisation Routes
    Route::post('/organisations', [OrganisationController::class, 'store']);
    Route::get('/organisations/{orgId}', [OrganisationController::class, 'getOrganisation']);
});

//fs



Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/run-migration',function(){
    Artisan::call('optimize:clear');
    Artisan::call('migrate:fresh --seed');

    return "Migration executed successfully";
});
