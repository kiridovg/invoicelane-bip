<?php

use App\Exceptions\DueDateBeforeIssueDateException;
use App\Exceptions\InvoiceNotEditableException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(fn (InvoiceNotEditableException $e) => response()->json([
            'message' => $e->getMessage(),
            'errors' => ['status' => [$e->getMessage()]],
        ], Response::HTTP_CONFLICT));

        $exceptions->render(fn (DueDateBeforeIssueDateException $e) => response()->json([
            'message' => $e->getMessage(),
            'errors' => ['due_date' => [$e->getMessage()]],
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    })->create();
