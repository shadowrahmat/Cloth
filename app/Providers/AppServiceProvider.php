<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
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
        // Each limiter counts twice: per IP, and per IP + guest session. The session part keeps
        // customers behind one shared IP (office, mobile network) from blocking each other; the
        // IP part stops someone who simply invents a new X-Session-ID on every request.
        $limit = fn (string $name, int $perMinute, ?int $perSession = null) => RateLimiter::for(
            $name,
            fn (Request $request) => [
                Limit::perMinute($perMinute * 5)->by("{$name}:ip:{$request->ip()}"),
                Limit::perMinute($perSession ?? $perMinute)
                    ->by("{$name}:session:{$request->ip()}|".$request->header('X-Session-ID')),
            ],
        );

        $limit('track-order', 5);
        $limit('coupon', 10);
        $limit('contact', 5);
        $limit('checkout', 10);
        $limit('cart', 60);
    }
}
