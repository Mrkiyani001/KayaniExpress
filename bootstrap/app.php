<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return null;
            }
        });
        $middleware->alias([
            'single.device' => \App\Http\Middleware\SingleDeviceLogin::class,
            'microservice.auth' => \App\Http\Middleware\microservice_auth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if($request->is('api/*')){
                return response()->json([
                    'message' => $e->getMessage(),
                ], 401);
            }
        });
        $exceptions->render(function(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request){
            if($request->is('api/*')){
                return response()->json([
                    'message' => $e->getMessage(),
                ], 404);
            }
        });
        
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });
        $exceptions->render(function (ModelNotFoundException $e, \Illuminate\Http\Request $request){
            if($request->is('api/*')){
                return response()->json([
                    'status' => false,
                    'message' => 'Resource not found',
                    'errors' => $e->getMessage(),
                ], 404);
            }
        });
    })->create();
