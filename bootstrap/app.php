<?php

use App\Http\Middleware\AuthenticateApiKey;
use App\Http\Middleware\EnsureOrganizationAdmin;
use App\Http\Middleware\EnsureSuperAdmin;
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
            'api.key' => AuthenticateApiKey::class,
            'super.admin' => EnsureSuperAdmin::class,
            'org.admin' => EnsureOrganizationAdmin::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/razorpay',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                if ($status >= 500) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Something went wrong. Please try again later.',
                    ], 500);
                }
            }

            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Illuminate\Session\TokenMismatchException) {
                return null;
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                && $e->getStatusCode() < 500) {
                return null;
            }

            if ($request->routeIs('org.campaigns.create', 'org.campaigns.store', 'org.campaigns.show')) {
                report($e);
                @file_put_contents(
                    storage_path('logs/campaign-debug.log'),
                    '['.date('Y-m-d H:i:s').'] '.$e::class.': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine().PHP_EOL.$e->getTraceAsString().PHP_EOL,
                    FILE_APPEND
                );

                $message = $request->routeIs('org.campaigns.store')
                    ? 'Could not save campaign: '.$e->getMessage()
                    : 'Could not open campaign: '.$e->getMessage();

                return redirect()
                    ->route('org.campaigns.index')
                    ->with('error', $message);
            }

            return null;
        });
    })->create();
