<?php

use App\Exceptions\Files\UnsupportedUploadException;
use App\Http\Middleware\AuthenticateApiToken;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'api.auth' => AuthenticateApiToken::class,
        ]);
        $middleware->redirectGuestsTo(fn (Request $request): ?string => $request->is('admin*') ? route('admin.login') : null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // The uploader is a domain service and raises its own exception; the
        // HTTP status for it is decided here, at the edge.
        $exceptions->render(function (UnsupportedUploadException $e, Request $request) {
            return $request->is('api/*')
                ? response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => []], 422)
                : back()->withInput()->with('error', $e->getMessage());
        });
    })->create();
