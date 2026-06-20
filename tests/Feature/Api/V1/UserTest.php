<?php

use App\Models\User;

test('authenticated user can view their profile', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/user');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
        ])
        ->assertJsonPath('user.id', $user->id);
});

test('unauthenticated user cannot view profile', function () {
    $response = $this->getJson('/api/v1/user');

    $response->assertStatus(401);
});

test('authenticated user can update their profile', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/user/profile', [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Profile updated successfully.'])
        ->assertJsonPath('user.name', 'Updated Name');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
    ]);
});

test('authenticated user can update their password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
    ]);
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->putJson('/api/v1/user/password', [
            'current_password' => 'old-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Password updated successfully.']);
});
