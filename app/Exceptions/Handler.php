<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        ValidationException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
    ];

    public function context(): array
    {
        $requestId = request()->header('X-Request-ID');

        if ($requestId && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $requestId)) {
            $requestId = '(invalid)';
        }

        return array_merge(parent::context(), [
            'userId' => auth('sanctum')->id(),
            'requestId' => $requestId,
            'ip' => request()->ip(),
        ]);
    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            return $this->renderJsonException($request, $e);
        }

        // Redirect unauthenticated users to login on 404 errors instead of showing the 404 page
        if ($e instanceof HttpException && $e->getStatusCode() === 404 && !auth()->check()) {
            return redirect()->route('login');
        }

        return parent::render($request, $e);
    }

    private function renderJsonException(Request $request, Throwable $e): JsonResponse
    {
        $status = $this->getStatusCode($e);
        $message = $this->getMessage($e, $status);
        $errorCode = $this->getErrorCode($e);
        $errors = $this->getValidationErrors($e);
        $details = config('app.debug') ? $this->getDebugDetails($e) : null;

        $response = ['message' => $message, 'error_code' => $errorCode];

        if ($errors) {
            $response['errors'] = $errors;
        }

        if ($details) {
            $response['details'] = $details;
        }

        return response()->json($response, $status);
    }

    private function getStatusCode(Throwable $e): int
    {
        return match (true) {
            $e instanceof HttpException => $e->getStatusCode(),
            $e instanceof ValidationException => 422,
            $e instanceof \InvalidArgumentException => 422,
            $e instanceof \App\Exceptions\EquipmentUnavailableException => 409,
            $e instanceof \App\Exceptions\InvalidStateTransitionException => 422,
            $e instanceof \Illuminate\Auth\AuthenticationException => 401,
            $e instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
            $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 404,
            default => 500,
        };
    }

    private function getMessage(Throwable $e, int $status): string
    {
        if ($e instanceof ValidationException) {
            return __('messages.http.422');
        }

        // DB exceptions always show a generic message — never expose SQL, table names, or column details
        if ($e instanceof QueryException) {
            return __('messages.http.500');
        }

        if (!config('app.debug')) {
            return match ($status) {
                401 => __('messages.http.401'),
                403 => __('messages.http.403'),
                404 => __('messages.http.404'),
                422 => __('messages.http.422'),
                default => __('messages.http.500'),
            };
        }

        // In debug mode: show the application message but never raw DB/system output
        return $e->getMessage() ?: __('messages.http.500');
    }

    private function getErrorCode(Throwable $e): string
    {
        return match (true) {
            $e instanceof ValidationException => 'VALIDATION_ERROR',
            $e instanceof \App\Exceptions\EquipmentUnavailableException => 'EQUIPMENT_UNAVAILABLE',
            $e instanceof \App\Exceptions\InvalidStateTransitionException => 'INVALID_STATE_TRANSITION',
            $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 'NOT_FOUND',
            $e instanceof \Illuminate\Auth\AuthenticationException => 'AUTH_REQUIRED',
            $e instanceof \Illuminate\Auth\Access\AuthorizationException => 'ACCESS_DENIED',
            $e instanceof QueryException => 'DATABASE_ERROR',
            default => 'SERVER_ERROR',
        };
    }

    private function getValidationErrors(Throwable $e): ?array
    {
        if ($e instanceof ValidationException) {
            return $e->errors();
        }

        return null;
    }

    private function getDebugDetails(Throwable $e): ?array
    {
        // Never expose DB internals (SQL, table names, column names) even in debug mode
        if ($e instanceof QueryException) {
            return ['exception' => get_class($e)];
        }

        return [
            'exception' => get_class($e),
            'file' => $this->stripBasePath($e->getFile()),
            'line' => $e->getLine(),
            'trace' => $this->getStackTrace($e),
        ];
    }

    private function getStackTrace(Throwable $e): array
    {
        $trace = [];
        foreach (array_slice($e->getTrace(), 0, 5) as $frame) {
            $trace[] = [
                'file' => $this->stripBasePath($frame['file'] ?? 'unknown'),
                'line' => $frame['line'] ?? 0,
                'function' => $frame['function'] ?? 'unknown',
            ];
        }

        return $trace;
    }

    // Strip the absolute server path — show only app-relative paths like app/Features/...
    private function stripBasePath(string $path): string
    {
        $base = base_path() . DIRECTORY_SEPARATOR;
        return str_starts_with($path, $base) ? substr($path, strlen($base)) : basename($path);
    }
}
