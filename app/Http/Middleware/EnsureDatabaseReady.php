<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\BufferedOutput;

class EnsureDatabaseReady
{
    private static bool $ready = false;

    public function __construct(private readonly Application $app) {}

    public function handle(Request $request, Closure $next)
    {
        if (! self::$ready && $this->app->isProduction() && config('database.default') === 'pgsql') {
            if (! Schema::hasTable('users')) {
                Artisan::call('migrate', ['--force' => true], new BufferedOutput);
                Artisan::call('db:seed', ['--force' => true], new BufferedOutput);
            }

            self::$ready = true;
        }

        return $next($request);
    }
}
