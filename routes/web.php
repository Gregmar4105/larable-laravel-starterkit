<?php

use App\Http\Controllers\Larable\DashboardController;
use App\Http\Controllers\Larable\ApiPlaygroundController;
use App\Http\Controllers\Larable\DatabaseController;
use App\Http\Controllers\Larable\GraphController;
use App\Http\Controllers\Larable\EmailTestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Larable Backend GUI
|--------------------------------------------------------------------------
|
| The Larable dashboard provides:
| - API endpoint explorer & playground
| - PostgreSQL database management with ER diagrams
| - Obsidian-style documentation graph
| - Email testing via Mailpit
|
*/
if (config('app.debug')) {
    Route::prefix('larable')->name('larable.')->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/endpoints', [DashboardController::class, 'endpoints'])->name('endpoints');

        // API Playground
        Route::post('/playground/execute', [ApiPlaygroundController::class, 'execute'])->name('playground.execute');

        // Database Management
        Route::get('/database/tables', [DatabaseController::class, 'tables'])->name('database.tables');
        Route::get('/database/table/{name}', [DatabaseController::class, 'tableData'])->name('database.table-data');
        Route::get('/database/schema', [DatabaseController::class, 'schema'])->name('database.schema');
        Route::post('/database/query', [DatabaseController::class, 'executeQuery'])->name('database.query');

        // Obsidian Graph
        Route::get('/graph', [GraphController::class, 'index'])->name('graph');
        Route::get('/graph/file/{path}', [GraphController::class, 'fileContent'])->name('graph.file')
            ->where('path', '.*');

        // Email Testing
        Route::post('/email/send', [EmailTestController::class, 'send'])->name('email.send');
        Route::get('/email/inbox', [EmailTestController::class, 'inbox'])->name('email.inbox');
        Route::get('/email/message/{id}', [EmailTestController::class, 'message'])->name('email.message');
        Route::delete('/email/clear', [EmailTestController::class, 'clear'])->name('email.clear');
    });
}
