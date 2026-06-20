<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // Register a test route with idempotency middleware
    Route::middleware(['api', 'idempotency'])->post('/test/idempotency', function () {
        return response()->json(['value' => uniqid()]);
    });
});

test('first request executes normally', function () {
    $response = $this->postJson('/test/idempotency', [], [
        'Idempotency-Key' => 'test-key-1',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['value']);

    expect($response->headers->get('X-Idempotency-Replay'))->toBeNull();
});

test('replay request returns cached response with replay header', function () {
    // First request
    $first = $this->postJson('/test/idempotency', [], [
        'Idempotency-Key' => 'test-key-replay',
    ]);

    $firstValue = $first->json('value');

    // Second request with same key
    $second = $this->postJson('/test/idempotency', [], [
        'Idempotency-Key' => 'test-key-replay',
    ]);

    $second->assertStatus(200);
    expect($second->json('value'))->toBe($firstValue);
    expect($second->headers->get('X-Idempotency-Replay'))->toBe('true');
});

test('different idempotency keys produce different responses', function () {
    $first = $this->postJson('/test/idempotency', [], [
        'Idempotency-Key' => 'key-a',
    ]);

    $second = $this->postJson('/test/idempotency', [], [
        'Idempotency-Key' => 'key-b',
    ]);

    expect($first->json('value'))->not->toBe($second->json('value'));
});

test('GET requests bypass idempotency', function () {
    Route::middleware(['api', 'idempotency'])->get('/test/idempotency-get', function () {
        return response()->json(['value' => uniqid()]);
    });

    $first = $this->getJson('/test/idempotency-get', [
        'Idempotency-Key' => 'get-key',
    ]);

    $second = $this->getJson('/test/idempotency-get', [
        'Idempotency-Key' => 'get-key',
    ]);

    // GET should produce different values since idempotency only applies to POST/PUT/PATCH
    expect($first->json('value'))->not->toBe($second->json('value'));
});

test('request without idempotency key proceeds normally', function () {
    $response = $this->postJson('/test/idempotency');

    $response->assertStatus(200)
        ->assertJsonStructure(['value']);
});
