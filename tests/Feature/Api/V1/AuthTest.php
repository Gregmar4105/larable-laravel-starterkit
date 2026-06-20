<?php

use App\Models\User;

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
            'token_type',
        ])
        ->assertJson(['token_type' => 'Bearer']);
});

test('login fails with invalid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('login fails with missing fields', function () {
    $response = $this->postJson('/api/v1/auth/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('user can register with valid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
            'token_type',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@example.com',
    ]);
});

test('register fails with duplicate email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully logged out.']);

    // Token should be revoked
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
    ]);
});

test('unauthenticated user cannot logout', function () {
    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(401);
});

test('csrf cookie endpoint is accessible', function () {
    $response = $this->getJson('/api/v1/auth/csrf-cookie');

    $response->assertStatus(200)
        ->assertJson(['message' => 'CSRF cookie set']);
});
