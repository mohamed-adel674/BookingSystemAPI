<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // API requests should return JSON responses
        if ($request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions and return JSON responses.
     */
    private function handleApiException($request, Throwable $e)
    {
        // Authentication Exception
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Please login to access this resource.'
            ], 401);
        }

        // Validation Exception
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        }

        // Model Not Found Exception
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'message' => 'Resource not found.',
                'error' => 'The requested resource does not exist.'
            ], 404);
        }

        // Not Found HTTP Exception
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Endpoint not found.',
                'error' => 'The requested endpoint does not exist.'
            ], 404);
        }

        // Method Not Allowed Exception
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'message' => 'Method not allowed.',
                'error' => 'The HTTP method is not supported for this endpoint.'
            ], 405);
        }

        // Default to 500 server error
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        
        return response()->json([
            'message' => 'Server error.',
            'error' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.'
        ], $statusCode);
    }
}
