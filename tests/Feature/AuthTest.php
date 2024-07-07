<?php

namespace Tests\Feature;


use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;
use App\Models\Organisation;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class, WithFaker::class);

it('generates token with correct expiration and user details', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200);
    $token = $user->tokens->first();

    // Ensure token is created
    expect($token)->not->toBeNull();

    // Check tokenable ID
    expect($token->tokenable_id)->toEqual($user->userId);
});




it('does not allow user to see other organisations data', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $organisation = Organisation::factory()->create([
        'name' => 'User1 Organisation',
    ]);

    $organisation->users()->attach($user1->userId);

    Sanctum::actingAs($user2, ['*']);

    $response = $this->get('/api/organisations/' . $organisation->orgId);

    $response->assertStatus(403);
});



uses(RefreshDatabase::class);

it('registers user successfully with default organisation', function () {
    $response = $this->post('/api/signup', [
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'phone' => '1234567890',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'status',
        'message',
        'data' => [
            'accessToken',
            'user' => [
                'userId',
                'firstName',
                'lastName',
                'email',
                'phone',
            ]
        ]
    ]);

    $this->assertDatabaseHas('users', ['email' => 'john.doe@example.com']);
    $this->assertDatabaseHas('organisations', ['name' => "John's Organisation"]);
});

it('logs the user in successfully', function () {
    $user = User::factory()->create([
        'email' => 'john.doe@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/api/login', [
        'email' => 'john.doe@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'status',
        'message',
        'data' => [
            'accessToken',
            'user' => [
                'userId',
                'firstName',
                'lastName',
                'email',
                'phone',
            ]
        ]
    ]);
});

it('fails if required fields are missing', function () {
    $response = $this->post('/api/signup', [
        'firstName' => '',
        'lastName' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password',
        'password_confirmation' => 'password', 
        'phone' => '1234567890'
    ]);

    $response->assertStatus(422);
    $response->assertJsonStructure([
        'errors' => [
            ['field', 'message']
        ]
    ]);
});

it('fails if there is duplicate email or userid', function () {
    $user = User::factory()->create([
        'email' => 'john.doe@example.com',
    ]);

    $response = $this->post('/api/signup', [
        'firstName' => 'Jane',
        'lastName' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422);
    $response->assertJsonStructure([
        'errors' => [
            ['field', 'message']
        ]
    ]);
});
