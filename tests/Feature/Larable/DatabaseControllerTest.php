<?php

test('database tables endpoint returns table list', function () {
    config(['app.debug' => true]);

    $response = $this->withSession(['larable_authenticated' => true])
        ->getJson(route('larable.database.tables'));

    $response->assertStatus(200)
        ->assertJsonIsArray();

    // Should contain at least the users table
    $tables = collect($response->json());
    expect($tables->pluck('name'))->toContain('users');
});

test('database table data endpoint returns paginated data', function () {
    config(['app.debug' => true]);

    $response = $this->withSession(['larable_authenticated' => true])
        ->getJson(route('larable.database.table-data', ['name' => 'users']));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'table',
            'columns',
            'data',
            'pagination' => ['current_page', 'per_page', 'total', 'last_page'],
        ]);
});

test('database table data returns 404 for non-existent table', function () {
    config(['app.debug' => true]);

    $response = $this->withSession(['larable_authenticated' => true])
        ->getJson(route('larable.database.table-data', ['name' => 'nonexistent_table']));

    $response->assertStatus(404);
});

test('database schema endpoint returns nodes and edges', function () {
    config(['app.debug' => true]);

    $response = $this->withSession(['larable_authenticated' => true])
        ->getJson(route('larable.database.schema'));

    $response->assertStatus(200)
        ->assertJsonStructure(['nodes', 'edges']);
});

test('sql query execution works for select queries', function () {
    config(['app.debug' => true]);

    $response = $this->withSession(['larable_authenticated' => true])
        ->postJson(route('larable.database.query'), [
            'query' => 'SELECT 1 as test_value',
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['type', 'columns', 'data', 'duration_ms'])
        ->assertJson(['type' => 'select']);
});

test('sql query blocks non-select queries in read-only mode', function () {
    config(['app.debug' => true, 'app.larable_sql_readonly' => true]);

    $response = $this->withSession(['larable_authenticated' => true])
        ->postJson(route('larable.database.query'), [
            'query' => 'DELETE FROM users WHERE id = 999999',
        ]);

    $response->assertStatus(403);
});

test('sql query rejects empty queries', function () {
    config(['app.debug' => true]);

    $response = $this->withSession(['larable_authenticated' => true])
        ->postJson(route('larable.database.query'), [
            'query' => '',
        ]);

    $response->assertStatus(422);
});

test('sql query rejects queries exceeding max length', function () {
    config(['app.debug' => true]);

    $response = $this->withSession(['larable_authenticated' => true])
        ->postJson(route('larable.database.query'), [
            'query' => 'SELECT '.str_repeat('a', 10001),
        ]);

    $response->assertStatus(422);
});
