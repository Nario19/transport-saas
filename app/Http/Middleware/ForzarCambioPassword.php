<?php
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
 
class ForzarCambioPassword
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
 
        if (!$user) {
            return $next($request);
        }
 
        // 1. Conductor
        if ($user->hasRole('conductor')) {
            $conductor = $user->conductor;
            if ($conductor?->primer_ingreso && !$request->is('conductor/cambiar-password*')) {
                return redirect()->route('conductor.cambiar-password');
            }
        }

        // 2. Propietario
        if ($user->hasRole('propietario')) {
            $propietario = $user->propietario;
            if ($propietario?->primer_ingreso && !$request->is('propietario/cambiar-password*')) {
                return redirect()->route('propietario.cambiar-password');
            }
        }
 
        return $next($request);
    }
}
