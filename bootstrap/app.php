<?php

use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\ApplyLocale;
use App\Http\Middleware\CourierOnly;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['admin' => AdminOnly::class, 'courier' => CourierOnly::class]);
        $middleware->web(append: [ApplyLocale::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {})
    ->create();
