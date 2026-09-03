<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PropietarioAccesoController extends Controller
{
    /**
     * Crear acceso móvil/web para el propietario usando su DNI.
     */
    public function store(Request $request, Propietario $propietario)
    {
        $user = Auth::user();
        abort_if($propietario->empresa_id !== $user->empresa_id, 403);

        if ($propietario->user()->exists()) {
            return back()->with('error', 'Este propietario ya tiene un usuario creado.');
        }

        $dni = trim($propietario->dni);
        if (empty($dni)) {
            return back()->with('error', 'El propietario debe tener un DNI registrado para crear su acceso.');
        }

        // Verificar si el DNI ya está en uso por otro usuario
        $existingUser = User::withTrashed()->where('email', $dni)->first();
        if ($existingUser) {
            if ($existingUser->propietario_id === $propietario->id) {
                $existingUser->restore();
                $existingUser->update([
                    'password' => Hash::make($dni),
                    'activo'   => true,
                ]);
                $propietario->update(['primer_ingreso' => true]);
                return back()->with('success', "Acceso restaurado. Usuario: \"{$dni}\" y contraseña: \"{$dni}\"");
            } else {
                return back()->with('error', "El DNI {$dni} ya está registrado como usuario en el sistema.");
            }
        }

        $nuevoUser = User::create([
            'empresa_id'     => $propietario->empresa_id,
            'propietario_id' => $propietario->id,
            'name'           => $propietario->nombre_completo,
            'email'          => $dni,
            'password'       => Hash::make($dni),
            'activo'         => true,
        ]);

        $rolePropietario = Role::firstOrCreate(['name' => 'propietario', 'guard_name' => 'web']);
        $nuevoUser->assignRole($rolePropietario);
        $propietario->update(['primer_ingreso' => true]);

        return back()->with('success', "Acceso creado. Usuario: \"{$dni}\" y contraseña inicial: \"{$dni}\"");
    }

    /**
     * Activar o desactivar cuenta del propietario.
     */
    public function toggle(Propietario $propietario)
    {
        $user = Auth::user();
        abort_if($propietario->empresa_id !== $user->empresa_id, 403);

        $usuario = $propietario->user;
        if (!$usuario) {
            return back()->with('error', 'No existe un usuario para este propietario.');
        }

        $usuario->update([
            'activo' => !$usuario->activo
        ]);

        $estado = $usuario->activo ? 'activado' : 'desactivado';
        return back()->with('success', "Acceso {$estado} correctamente.");
    }

    /**
     * Resetear contraseña al DNI del propietario.
     */
    public function resetPassword(Propietario $propietario)
    {
        $user = Auth::user();
        abort_if($propietario->empresa_id !== $user->empresa_id, 403);

        $dni = trim($propietario->dni);
        if (empty($dni)) {
            return back()->with('error', 'El propietario no tiene DNI registrado.');
        }

        $usuario = $propietario->user;
        if (!$usuario) {
            return back()->with('error', 'El propietario no tiene un usuario creado.');
        }

        $usuario->update([
            'email'    => $dni,
            'password' => Hash::make($dni),
            'activo'   => true,
        ]);

        $propietario->update(['primer_ingreso' => true]);

        return back()->with('success', "Contraseña reiniciada. Usuario: \"{$dni}\" y nueva clave temporal: \"{$dni}\".");
    }

    /**
     * Eliminar credenciales de acceso.
     */
    public function destroy(Propietario $propietario)
    {
        $user = Auth::user();
        abort_if($propietario->empresa_id !== $user->empresa_id, 403);

        $usuario = User::withTrashed()->where('propietario_id', $propietario->id)->first();
        if ($usuario) {
            $usuario->forceDelete();
            $propietario->update(['primer_ingreso' => false]);
        }

        return back()->with('success', 'Credenciales del propietario eliminadas.');
    }
}
