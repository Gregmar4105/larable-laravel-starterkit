<?php

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;

test('larable routes are accessible when debug mode is enabled', function () {
    config(['app.debug' => true]);
    config(['app.larable_password' => 'larable']);

    $response = $this->get('/larable');
    $response->assertStatus(401);

    $response = $this->withSession(['larable_authenticated' => true])->get('/larable');
    $response->assertStatus(200);
});

test('larable routes are not registered or accessible when debug mode is disabled', function () {
    config(['app.debug' => false]);

    // Clear currently registered routes and re-load web.php routes
    $router = app('router');
    $router->setRoutes(new RouteCollection);

    require base_path('routes/web.php');

    // Assert Route named helper does not have the larable.dashboard route
    expect(Route::has('larable.dashboard'))->toBeFalse();

    // Assert visiting /larable returns 404
    $response = $this->get('/larable');
    $response->assertStatus(404);
});

test('larable password submission authenticates the session', function () {
    config(['app.debug' => true]);
    config(['app.larable_password' => 'secret-password']);

    // Send wrong password
    $response = $this->post('/larable', [
        'larable_password' => 'wrong-password',
    ]);
    $response->assertStatus(401);
    $this->assertNull(session('larable_authenticated'));

    // Send correct password
    $response = $this->post('/larable', [
        'larable_password' => 'secret-password',
    ]);
    $response->assertRedirect(route('larable.dashboard'));
    expect(session('larable_authenticated'))->toBeTrue();
});
