<?php

test('api version middleware adds X-API-Version header', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertStatus(200);
    expect($response->headers->get('X-API-Version'))->toBe('v1');
});
