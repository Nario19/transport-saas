<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Requests\UpdateEmpresaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    /**
     * Dashboard Maestro para German (SUPER_ADMIN)
     */
    public function dashboard()
    {
        return view('superadmin.index');
    }

    /**
     * Listado de todas las empresas.
     */
    public function index()
    {
        $empresas = Empresa::orderBy('created_at', 'desc')->get();
        return view('superadmin.empresas.index', compact('empresas'));
    }

    /**
     * Formulario para crear empresa.
     */
    public function create()
    {
        return view('superadmin.empresas.create');
    }

    /**
     * Guardar nueva empresa en la base de datos.
     */
    public function store(StoreEmpresaRequest $request)
    {
        $data = $request->validated();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $data) {
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos', 'public');
            }

            // Filtrar datos exclusivos de la empresa para su creación (evitar columna inexistente 'logo')
            $empresaData = collect($data)
                ->except(['admin_name', 'admin_email', 'password', 'password_confirmation', 'logo'])
                ->toArray();

            if ($logoPath) {
                $empresaData['logo_path'] = $logoPath;
            }

            $empresa = Empresa::create($empresaData);

            // Crear los roles correspondientes para la nueva empresa
            $prefijo = 'e' . $empresa->id . '_';
            
            $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $prefijo . 'ADMIN', 'guard_name' => 'web']
            );

            \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => $prefijo . 'OPERADOR', 'guard_name' => 'web']
            );

            // Permisos base del Administrador de esta empresa
            $permisos = [
                'ver dashboard',
                'ver vehiculos',
                'ver conductores',
                'ver propietarios',
                'ver rutas',
                'ver vueltas',
                'ver tributos',
                'ver sanciones',
                'ver reportes',
                'gestionar usuarios',
                'gestionar roles',
                'gestionar ajustes de empresa',
                'gestionar backups',
                'gestionar alertas',
            ];

            foreach ($permisos as $p) {
                \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            }

            $adminRole->syncPermissions($permisos);

            // Crear el usuario administrador asociado a la empresa
            $user = \App\Models\User::create([
                'empresa_id'   => $empresa->id,
                'name'         => $data['admin_name'],
                'email'        => $data['admin_email'],
                'password'     => \Illuminate\Support\Facades\Hash::make($data['password']),
                'activo'       => true,
            ]);

            $user->syncRoles([$adminRole]);

            return redirect()->route('superadmin.empresas.index')
                ->with('success', "Empresa \"{$empresa->nombre}\" y su usuario Administrador registrados correctamente.");
        });
    }

    /**
     * Formulario de edición.
     */
    public function edit(Empresa $empresa)
    {
        $prefijo = 'e' . $empresa->id . '_';
        $admin = \App\Models\User::role($prefijo . 'ADMIN')->first();

        return view('superadmin.empresas.edit', compact('empresa', 'admin'));
    }

    /**
     * Actualizar los datos de la empresa.
     */
    public function update(UpdateEmpresaRequest $request, Empresa $empresa)
    {
        $data = $request->validated();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $data, $empresa) {
            $logoPath = $empresa->logo_path;
            if ($request->hasFile('logo')) {
                // Borrar logo anterior si existe
                if ($empresa->logo_path) {
                    Storage::disk('public')->delete($empresa->logo_path);
                }
                $logoPath = $request->file('logo')->store('logos', 'public');
            }

            // Filtrar datos exclusivos de la empresa para su actualización
            $empresaData = collect($data)
                ->except(['admin_name', 'admin_email', 'admin_password', 'admin_password_confirmation', 'logo'])
                ->toArray();

            if ($request->hasFile('logo')) {
                $empresaData['logo_path'] = $logoPath;
            }

            if (isset($data['activa'])) {
                $empresaData['activa'] = (bool) $data['activa'];
            }

            $empresa->update($empresaData);

            // Actualizar el administrador principal de la empresa si se enviaron datos
            $prefijo = 'e' . $empresa->id . '_';
            $admin = \App\Models\User::role($prefijo . 'ADMIN')->first();

            if ($admin) {
                $adminData = [];
                if (!empty($data['admin_name'])) {
                    $adminData['name'] = $data['admin_name'];
                }
                if (!empty($data['admin_email']) && $data['admin_email'] !== $admin->email) {
                    $request->validate([
                        'admin_email' => 'unique:users,email,' . $admin->id,
                    ], [
                        'admin_email.unique' => 'El correo electrónico ya está registrado en el sistema.',
                    ]);
                    $adminData['email'] = $data['admin_email'];
                }
                if (!empty($data['admin_password'])) {
                    $adminData['password'] = \Illuminate\Support\Facades\Hash::make($data['admin_password']);
                }

                if (!empty($adminData)) {
                    $admin->update($adminData);
                }
            }

            return redirect()->route('superadmin.empresas.index')
                ->with('success', "Empresa \"{$empresa->nombre}\" actualizada correctamente.");
        });
    }

    /**
     * Interruptor de encendido/apagado del servicio (activa).
     */
    public function toggleStatus(Empresa $empresa)
    {
        $empresa->update([
            'activa' => !$empresa->activa
        ]);

        $status = $empresa->activa ? 'activada (Acceso Permitido)' : 'suspendida (Acceso Denegado)';
        
        return back()->with('success', "La empresa {$empresa->nombre} ha sido {$status}.");
    }

    /**
     * Eliminación de empresa (Soft Delete) y desactivación de sus accesos.
     */
    public function destroy(Empresa $empresa)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($empresa) {
            $nombre = $empresa->nombre;

            // Desactivar y eliminar (soft-delete) los usuarios asociados
            $empresa->users()->update(['activo' => false]);
            $empresa->users()->delete();

            // Eliminar (soft-delete) la empresa
            $empresa->delete();

            return redirect()->route('superadmin.empresas.index')
                ->with('success', "La empresa \"{$nombre}\" ha sido eliminada del sistema.");
        });
    }
}