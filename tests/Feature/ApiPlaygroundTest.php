<?php

test('playground execution route handles local endpoints internally and successfully', function () {
    $response = $this->withoutMiddleware()
        ->postJson(route('larable.playground.execute'), [
            'method' => 'GET',
            'url' => '/api/v1/health',
            'body' => [],
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'status_text',
            'headers',
            'body',
            'duration_ms',
            'size_bytes',
        ]);

    $body = $response->json('body');
    expect($body['status'])->toBe('ok');
    expect($body['version'])->toBe('v1');
});
