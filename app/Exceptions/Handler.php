<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [];

    /**
     * Inputs that are never flashed for validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {

            // ✅ Validation errors
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'data'    => null,
                    'errors'  => $exception->errors(),
                    'meta'    => null,
                ], 422);
            }

            // ✅ HTTP exceptions (403, 404, etc.)
            if ($exception instanceof HttpExceptionInterface) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage() ?: 'Request failed.',
                    'data'    => null,
                    'errors'  => null,
                    'meta'    => null,
                ], $exception->getStatusCode());
            }

            // ✅ Unhandled server errors
            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? $exception->getMessage()
                    : 'Server error.',
                'data'    => null,
                'errors'  => null,
                'meta'    => null,
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
