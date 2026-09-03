<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $propietario = $user->propietario?->load(['empresa', 'vehiculos']);

        return view('users.propietario.perfil', compact('propietario'));
    }

    public function cambiarPassword()
    {
        return view('users.propietario.cambiar-password');
    }

    public function guardarPassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ], [
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $propietario = $user->propietario;
        if ($propietario) {
            $propietario->update(['primer_ingreso' => false]);
        }

        return redirect()->route('propietario.dashboard')
            ->with('success', 'Contraseña actualizada correctamente.');
    }
}
