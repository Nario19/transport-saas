<?php
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
 
class RedirectSegunRol
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
 
        if (!$user) {
            return $next($request);
        }
 
        // Si es conductor y está intentando acceder a admin o propietario
        if ($user->hasRole('conductor') && ($request->is('dashboard') || $request->is('admin*') || $request->is('vehiculos*') || $request->is('conductores*') || $request->is('propietario/*'))) {
            return redirect()->route('conductor.dashboard');
        }
 
        // Si es propietario y está intentando acceder a admin o conductor
        if ($user->hasRole('propietario') && ($request->is('dashboard') || $request->is('admin*') || $request->is('vehiculos*') || $request->is('conductores*') || $request->is('conductor/*'))) {
            return redirect()->route('propietario.dashboard');
        }

        // Si no es conductor y está intentando acceder al panel conductor
        if (!$user->hasRole('conductor') && $request->is('conductor/*')) {
            if ($user->hasRole('propietario')) {
                return redirect()->route('propietario.dashboard');
            }
            return redirect()->route('dashboard');
        }

        // Si no es propietario y está intentando acceder al panel propietario
        if (!$user->hasRole('propietario') && $request->is('propietario/*')) {
            if ($user->hasRole('conductor')) {
                return redirect()->route('conductor.dashboard');
            }
            return redirect()->route('dashboard');
        }
 
        return $next($request);
    }
}
 
 