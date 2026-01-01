<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Configure security headers
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Get error handling service for consistent error processing
        $errorHandler = app(App\Services\ErrorHandlingService::class);

        // Register custom exception rendering for all document generation exceptions
        $exceptions->render(function (App\Exceptions\DocumentGenerationException $e, $request) {
            return $e->render($request);
        });

        // Handle validation exceptions specifically
        $exceptions->render(function (App\Exceptions\ValidationException $e, $request) {
            return $e->render($request);
        });

        // Handle content neutrality violations
        $exceptions->render(function (App\Exceptions\ContentNeutralityException $e, $request) {
            return $e->render($request);
        });

        // Handle template exceptions
        $exceptions->render(function (App\Exceptions\TemplateException $e, $request) {
            return $e->render($request);
        });

        // Handle country adaptation exceptions
        $exceptions->render(function (App\Exceptions\CountryAdaptationException $e, $request) {
            return $e->render($request);
        });

        // Handle output formatting exceptions
        $exceptions->render(function (App\Exceptions\OutputFormattingException $e, $request) {
            return $e->render($request);
        });

        // Handle data integrity exceptions
        $exceptions->render(function (App\Exceptions\DataIntegrityException $e, $request) {
            return $e->render($request);
        });

        // Handle common Laravel exceptions with user-friendly messages
        $exceptions->render(function (Illuminate\Database\QueryException $e, $request) {
            $errorHandler->logError('Database query exception', [
                'sql_state' => $e->errorInfo[0] ?? 'unknown',
                'error_code' => $e->errorInfo[1] ?? 'unknown',
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ], 'error');

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DATABASE_ERROR',
                    'message' => 'Erreur de base de données temporaire. Veuillez réessayer.',
                    'timestamp' => now()->toISOString()
                ]
            ], 500);
        });

        $exceptions->render(function (Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'Trop de requêtes. Veuillez patienter avant de réessayer.',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? 60,
                    'timestamp' => now()->toISOString()
                ]
            ], 429);
        });

        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RESOURCE_NOT_FOUND',
                        'message' => 'Ressource non trouvée.',
                        'timestamp' => now()->toISOString()
                    ]
                ], 404);
            }
            // Let Laravel handle non-JSON requests normally
            return null;
        });

        // Enhanced reportable exceptions with better context sanitization
        $exceptions->reportable(function (Throwable $e) use ($errorHandler) {
            // Don't double-report our custom exceptions (they log themselves)
            if ($e instanceof App\Exceptions\DocumentGenerationException) {
                return false;
            }

            // Don't report user errors that aren't system issues
            if (!$errorHandler->shouldReport($e)) {
                return false;
            }
            
            // Build comprehensive but sanitized context
            $context = [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace_hash' => md5($e->getTraceAsString()), // Hash for grouping without exposing paths
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip(),
                'session_id' => session()->getId(),
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString()
            ];

            // Sanitize and include request data
            $requestData = request()->except([
                'fields', 'password', 'password_confirmation', 
                'token', '_token', 'api_key', 'user_data',
                'applicant_name', 'student_name', 'declarant_name',
                'complainant_name', 'requester_name', 'appellant_name',
                'sender_name', 'address', 'phone', 'email'
            ]);
            
            if (!empty($requestData)) {
                $context['request_data'] = $requestData;
            }

            // Add previous exception context if available
            if ($e->getPrevious()) {
                $context['previous_exception'] = [
                    'class' => get_class($e->getPrevious()),
                    'message' => $e->getPrevious()->getMessage(),
                    'file' => $e->getPrevious()->getFile(),
                    'line' => $e->getPrevious()->getLine()
                ];
            }

            $errorHandler->logError($e->getMessage(), $context, 'error');
        });

        // Don't report certain exceptions to external services
        $exceptions->dontReport([
            App\Exceptions\ValidationException::class,
            App\Exceptions\ContentNeutralityException::class,
            App\Exceptions\UnsupportedCountryException::class,
            Illuminate\Http\Exceptions\ThrottleRequestsException::class,
            Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
            Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class
        ]);
    })->create();
