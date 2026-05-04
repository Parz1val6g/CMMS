<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Exceptions that should NOT be reported (user errors, expected conditions)
     */
    protected $dontReport = [
        ValidationException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
    ];

    /**
     * Build context for logging (userId, requestId, etc.)
     */
    public function context(): array
    {
        $requestId = request()->header('X-Request-ID');

        // Only log valid UUIDs — ignore user-injected values
        if ($requestId && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $requestId)) {
            $requestId = '(invalid)';
        }

        return array_merge(parent::context(), [
            'userId' => auth('sanctum')->id(),
            'requestId' => $requestId,
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Render exception as HTTP response
     * API requests (expectsJson) → JSON response
     * Web requests → HTML response
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            return $this->renderJsonException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Render exception as JSON for API requests
     * Structure: { "message": "...", "error_code": "...", "details": {...} }
     */
    private function renderJsonException(Request $request, Throwable $e): JsonResponse
    {
        $status = $this->getStatusCode($e);
        $message = $this->getMessage($e, $status);
        $errorCode = $this->getErrorCode($e);
        $details = config('app.debug') ? $this->getDebugDetails($e) : null;
        $errors = $this->getValidationErrors($e);

        $response = [
            'message' => $message,
            'error_code' => $errorCode,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        if ($details) {
            $response['details'] = $details;
        }

        return response()->json($response, $status);
    }

    /**
     * Determine HTTP status code from exception
     */
    private function getStatusCode(Throwable $e): int
    {
        return match (true) {
            $e instanceof HttpException => $e->getStatusCode(),
            $e instanceof ValidationException => 422,
            $e instanceof \InvalidArgumentException => 422,
            $e instanceof \App\Exceptions\EquipmentUnavailableException => 409,
            $e instanceof \Illuminate\Auth\AuthenticationException => 401,
            $e instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
            $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 404,
            default => 500,
        };
    }

    /**
     * Get human-readable error message
     * Production: Generic messages (no information leakage)
     * Debug: Actual exception message
     */
    private function getMessage(Throwable $e, int $status): string
    {
        if ($e instanceof ValidationException) {
            return 'Validation failed';
        }

        if (! config('app.debug')) {
            return match ($status) {
                401 => 'Authentication required',
                403 => 'Access denied',
                404 => 'Resource not found',
                422 => 'Validation failed',
                500 => 'An error occurred',
                default => 'An error occurred',
            };
        }

        return $e->getMessage() ?: 'An error occurred';
    }

    /**
     * Get machine-readable error code for frontend handling
     * Examples: VALIDATION_ERROR, AUTH_REQUIRED, NOT_FOUND, SERVER_ERROR
     */
    private function getErrorCode(Throwable $e): string
    {
        return match (true) {
            $e instanceof ValidationException => 'VALIDATION_ERROR',
            $e instanceof \App\Exceptions\EquipmentUnavailableException => 'EQUIPMENT_UNAVAILABLE',
            $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 'NOT_FOUND',
            $e instanceof \Illuminate\Auth\AuthenticationException => 'AUTH_REQUIRED',
            $e instanceof \Illuminate\Auth\Access\AuthorizationException => 'ACCESS_DENIED',
            default => 'SERVER_ERROR',
        };
    }

    /**
     * Extract validation errors from ValidationException
     */
    private function getValidationErrors(Throwable $e): ?array
    {
        if ($e instanceof ValidationException) {
            return $e->errors();
        }

        return null;
    }

    /**
     * Debug details (stack trace, file, line) — ONLY in development
     */
    private function getDebugDetails(Throwable $e): ?array
    {
        if (! config('app.debug')) {
            return null;
        }

        return [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $this->getStackTrace($e),
        ];
    }

    /**
     * Get simplified stack trace (first 5 frames)
     */
    private function getStackTrace(Throwable $e): array
    {
        $trace = [];
        foreach (array_slice($e->getTrace(), 0, 5) as $frame) {
            $trace[] = [
                'file' => $frame['file'] ?? 'unknown',
                'line' => $frame['line'] ?? 0,
                'function' => $frame['function'] ?? 'unknown',
            ];
        }

        return $trace;
    }
}
