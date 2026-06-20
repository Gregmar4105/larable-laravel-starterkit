<?php

test('health endpoint returns ok status', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertStatus(200)
        ->assertJsonStructure(['status', 'version', 'timestamp'])
        ->assertJson(['status' => 'ok', 'version' => 'v1']);
});

test('health endpoint does not expose debug flag', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertStatus(200);
    expect($response->json())->not->toHaveKey('debug');
});
