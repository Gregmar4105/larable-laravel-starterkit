<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('forgot password sends reset link email', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Password reset link sent to your email.']);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('forgot password fails with non-existent email', function () {
    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(422);
});

test('forgot password fails without email', function () {
    $response = $this->postJson('/api/v1/auth/forgot-password', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});
