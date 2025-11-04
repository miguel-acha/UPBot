<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        //
    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            $status = 500;

            if ($e instanceof ValidationException) {
                $status = 422;
            } elseif ($e instanceof AuthorizationException) {
                $status = 403;
            } elseif ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
            }

            if ($e instanceof QueryException) {
                Log::error('SQL ERROR', [
                    'sql'      => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'message'  => $e->getMessage(),
                ]);
            } else {
                Log::error('API ERROR', ['type' => class_basename($e), 'message' => $e->getMessage()]);
            }

            return response()->json([
                'message' => $e->getMessage(),
                'type'    => class_basename($e),
            ], $status);
        }

        return parent::render($request, $e);
    }
}
