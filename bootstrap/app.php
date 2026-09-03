<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Auth;

 
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'empresa.activa'     => \App\Http\Middleware\CheckEmpresaActiva::class,
            'forzar.password'    => \App\Http\Middleware\ForzarCambioPassword::class,
            'admin.configurado'  => \App\Http\Middleware\EnsureCompanyConfigured::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
            'api/*',
        ]);
        $middleware->redirectGuestsTo(fn() => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
 
            if ($e instanceof UnauthorizedException) {
                /** @var \App\Models\User|null $user */
                $user = Auth::user();
                
 
                // Sin sesión → login
                if (!$user) {
                    return redirect()->route('login')
                        ->with('error', 'Debes iniciar sesión para continuar.');
                }
 
                // Conductor en otra ruta → redirigir a su panel limpiamente
                if ($user->hasRole('conductor')) {
                    return redirect()->route('conductor.dashboard');
                }

                // Propietario en otra ruta → redirigir a su panel limpiamente
                if ($user->hasRole('propietario')) {
                    return redirect()->route('propietario.dashboard');
                }

                // SUPER_ADMIN → su panel
                if ($user->hasRole('SUPER_ADMIN')) {
                    return redirect()->route('superadmin.dashboard')
                        ->with('error', 'Acceso no autorizado.');
                }
 
                // Cualquier otro caso → login con mensaje
                // NO redirigir al dashboard porque puede causar loop
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')
                    ->with('error', 'No tienes permiso para acceder a esa sección. Contacta al administrador.');
            }
 
            if ($response->getStatusCode() === 419) {
                return redirect()->route('login')
                    ->with('info', 'Tu sesión ha expirado por inactividad.');
            }
 
            return $response;
        });
    })->create();
