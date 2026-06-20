<?php

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

test('user can enable 2FA', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/two-factor/enable');

    $response->assertStatus(200)
        ->assertJsonStructure(['message', 'qr_code', 'secret', 'recovery_codes']);

    $user->refresh();
    expect($user->two_factor_secret)->not->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
});

test('user can confirm 2FA with valid code', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    // First, enable 2FA to generate secret
    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/two-factor/enable');

    $user->refresh();
    $google2fa = new Google2FA;
    $validCode = $google2fa->getCurrentOtp(decrypt($user->two_factor_secret));

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/two-factor/confirm', [
            'code' => $validCode,
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Two-factor authentication confirmed and active.']);

    $user->refresh();
    expect($user->two_factor_confirmed_at)->not->toBeNull();
});

test('confirming 2FA fails with invalid code', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/two-factor/enable');

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/two-factor/confirm', [
            'code' => '000000',
        ]);

    $response->assertStatus(422);
});

test('user can disable 2FA', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    // Enable and confirm 2FA
    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/two-factor/enable');

    $user->refresh();
    $google2fa = new Google2FA;
    $validCode = $google2fa->getCurrentOtp(decrypt($user->two_factor_secret));

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/two-factor/confirm', [
            'code' => $validCode,
        ]);

    // Disable 2FA
    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson('/api/v1/auth/two-factor/disable');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Two-factor authentication disabled.']);

    $user->refresh();
    expect($user->two_factor_secret)->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
});

test('user can view QR code and recovery codes when 2FA enabled', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    // Enable 2FA
    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/two-factor/enable');

    // Get QR code
    $responseQr = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/two-factor/qr-code');

    $responseQr->assertStatus(200)
        ->assertJsonStructure(['svg', 'url']);

    // Get recovery codes
    $responseRc = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/two-factor/recovery-codes');

    $responseRc->assertStatus(200)
        ->assertJsonStructure(['recovery_codes']);

    // Regenerate recovery codes
    $responseRegen = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/two-factor/recovery-codes');

    $responseRegen->assertStatus(200)
        ->assertJsonStructure(['recovery_codes']);
});
