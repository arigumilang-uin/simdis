<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Exceptions\DomainException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Domain-specific routes (all under 'web' middleware group)
            Route::middleware('web')
                ->group(base_path('routes/siswa.php'));

            Route::middleware('web')
                ->group(base_path('routes/master_data.php'));

            Route::middleware('web')
                ->group(base_path('routes/pelanggaran.php'));

            Route::middleware('web')
                ->group(base_path('routes/tindak_lanjut.php'));

            Route::middleware('web')
                ->group(base_path('routes/user.php'));

            Route::middleware('web')
                ->group(base_path('routes/report.php'));

            Route::middleware('web')
                ->group(base_path('routes/developer.php'));

            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/legacy.php'));

            Route::middleware('web')
                ->group(base_path('routes/pembinaan.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aliases middleware kustom aplikasi
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'profile.completed' => \App\Http\Middleware\EnsureProfileCompleted::class,
            'account.active' => \App\Http\Middleware\CheckAccountActive::class,
        ]);
        
        // Apply CheckAccountActive to all authenticated routes
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\CheckAccountActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle DomainException dan child classes
        $exceptions->render(function (DomainException $e, $request) {
            // Log dengan context untuk debugging
            Log::error($e->getMessage(), $e->getLogContext());

            // Jika request expects JSON (API)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getUserMessage(),
                    'error_code' => get_class($e),
                ], $e->getHttpStatusCode());
            }

            // Jika request web (HTML)
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getUserMessage());
        });
    })->create();