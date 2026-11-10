<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class EnsureDatabaseReady
{
    private static bool $ready = false;

    public function __construct(private readonly Application $app) {}

    public function handle(Request $request, Closure $next)
    {
        if (! self::$ready && $this->app->isProduction() && config('database.default') === 'pgsql') {
            self::$ready = true;
        }

        return $next($request);
    }
}
