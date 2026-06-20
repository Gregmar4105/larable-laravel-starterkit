<?php

use Illuminate\Support\Facades\Route;

test('larable routes are accessible when debug mode is enabled', function () {
    config(['app.debug' => true]);

    $response = $this->get('/larable');
    $response->assertStatus(200);
});

test('larable routes are not registered or accessible when debug mode is disabled', function () {
    config(['app.debug' => false]);

    // Clear currently registered routes and re-load web.php routes
    $router = app('router');
    $router->setRoutes(new \Illuminate\Routing\RouteCollection());
    
    require base_path('routes/web.php');

    // Assert Route named helper does not have the larable.dashboard route
    expect(Route::has('larable.dashboard'))->toBeFalse();

    // Assert visiting /larable returns 404
    $response = $this->get('/larable');
    $response->assertStatus(404);
});
