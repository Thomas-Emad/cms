<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ResolveCurrentHotel;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->encryptCookies(except: [
            'locale',
        ]);

        $middleware->alias([
            'resolve.hotel' => ResolveCurrentHotel::class,
            'role' => EnsureUserHasRole::class,
        ]);

        $middleware->trustProxies(
            '*',
            Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $response;
            }

            $status = $response->getStatusCode();

            if (in_array($status, [401, 403, 404, 500, 503], true)) {
                if ($status === 500 && config('app.debug')) {
                    return $response;
                }

                return Inertia::render('Error', [
                    'status' => $status,
                    'message' => in_array($status, [401, 403], true) ? ($e->getMessage() ?: null) : null,
                    'csrf_token' => $request->hasSession() ? $request->session()->token() : null,
                    'user' => $request->user()?->only(['id', 'name', 'email', 'role']),
                ])->toResponse($request)->setStatusCode($status);
            }

            if ($status === 419) {
                return back()->with([
                    'message' => 'The page session expired, please try again.',
                ]);
            }

            return $response;
        });
    })->create();
