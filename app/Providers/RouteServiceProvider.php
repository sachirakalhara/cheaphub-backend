<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Auth endpoints get much tighter limits to stop password guessing and
        // email spam. Keys include the email, so one attacker cannot lock other
        // customers out even if many requests share an IP (e.g. behind a proxy).
        $tooMany = function () {
            return response()->json(
                ['message' => 'Too many attempts. Please wait a minute and try again.'],
                429
            );
        };
        $emailKey = function (Request $request) {
            return strtolower(trim((string) $request->input('email'))) . '|' . $request->ip();
        };

        RateLimiter::for('login', function (Request $request) use ($tooMany, $emailKey) {
            return [
                Limit::perMinute(5)->by('login:' . $emailKey($request))->response($tooMany),
                Limit::perMinute(30)->by('login-ip:' . $request->ip())->response($tooMany),
            ];
        });

        RateLimiter::for('register', function (Request $request) use ($tooMany, $emailKey) {
            return Limit::perMinute(5)->by('register:' . $emailKey($request))->response($tooMany);
        });

        RateLimiter::for('forgot-password', function (Request $request) use ($tooMany, $emailKey) {
            return Limit::perMinute(3)->by('forgot:' . $emailKey($request))->response($tooMany);
        });

        RateLimiter::for('reset-password', function (Request $request) use ($tooMany) {
            return Limit::perMinute(10)->by('reset:' . $request->ip())->response($tooMany);
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
