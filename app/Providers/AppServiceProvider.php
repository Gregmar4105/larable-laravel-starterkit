<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Prevent lazy loading in non-production environments to avoid N+1 query problems.
        Model::preventLazyLoading(! $this->app->isProduction());

        // API rate limiter: 60 requests per minute per authenticated user or IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Login rate limiter: 5 attempts per minute per email + IP
        RateLimiter::for('login', function (Request $request) {
            $key = strtolower($request->input('email', '')).'|'.$request->ip();

            return Limit::perMinute(5)->by($key);
        });
    }
}
