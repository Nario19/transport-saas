<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesYPermisosEmpresaTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa1;
    private Empresa $empresa2;
    private User $adminEmpresa1;
    private User $adminEmpresa2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Crear 2 empresas para pruebas de multi-tenancy
        $this->empresa1 = Empresa::create([
            'nombre' => 'Transportes Junín SAC',
            'ruc' => '20123456789',
            'plan' => 'enterprise',
            'tributo_diario' => 24.00,
            'activa' => true,
        ]);

        $this->empresa2 = Empresa::create([
            'nombre' => 'Transportes Huancayo SAC',
            'ruc' => '20987654321',
            'plan' => 'pro',
            'tributo_diario' => 20.00,
            'activa' => true,
        ]);

        // 2. Crear roles de administración para cada empresa
        $superRole = Role::findByName('SUPER_ADMIN', 'web');

        $roleAdminE1 = Role::firstOrCreate(
            ['name' => 'e' . $this->empresa1->id . '_ADMIN', 'guard_name' => 'web']
        );
        $roleAdminE2 = Role::firstOrCreate(
            ['name' => 'e' . $this->empresa2->id . '_ADMIN', 'guard_name' => 'web']
        );

        // 3. Crear administradores de cada empresa
        $this->adminEmpresa1 = User::create([
            'name' => 'Admin Empresa 1',
            'email' => 'admin_e1@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa1->id,
            'activo' => true,
        ]);
        $this->adminEmpresa1->assignRole($roleAdminE1);

        $this->adminEmpresa2 = User::create([
            'name' => 'Admin Empresa 2',
            'email' => 'admin_e2@transhuancayo.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa2->id,
            'activo' => true,
        ]);
        $this->adminEmpresa2->assignRole($roleAdminE2);
    }

    public function test_admin_empresa_puede_crear_rol_personalizado_con_prefijo_y_permisos(): void
    {
        Permission::firstOrCreate(['name' => 'ver tributos', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'cobrar tributo', 'guard_name' => 'web']);

        $response = $this->actingAs($this->adminEmpresa1)->post(route('roles.store'), [
            'name' => 'CAJERO',
            'permissions' => ['ver tributos', 'cobrar tributo'],
        ]);

        $response->assertRedirect(route('roles.index'));

        // El rol debe crearse con el prefijo de la empresa (e1_CAJERO)
        $roleName = 'e' . $this->empresa1->id . '_CAJERO';
        $this->assertDatabaseHas('roles', [
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        $role = Role::findByName($roleName, 'web');
        $this->assertTrue($role->hasPermissionTo('ver tributos'));
        $this->assertTrue($role->hasPermissionTo('cobrar tributo'));
    }

    public function test_aislamiento_multitenant_empresa_no_puede_ver_ni_editar_roles_de_otra_empresa(): void
    {
        // Rol perteneciente a Empresa 2
        $roleE2 = Role::create([
            'name' => 'e' . $this->empresa2->id . '_SUPERVISOR',
            'guard_name' => 'web',
        ]);

        // Rol perteneciente a Empresa 1
        $roleE1 = Role::create([
            'name' => 'e' . $this->empresa1->id . '_DESPACHADOR',
            'guard_name' => 'web',
        ]);

        // 1. En el index, el Admin de Empresa 1 solo debe ver su propio rol
        $responseIndex = $this->actingAs($this->adminEmpresa1)->get(route('roles.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('DESPACHADOR');
        $responseIndex->assertDontSee('SUPERVISOR');

        // 2. Si el Admin de Empresa 1 intenta editar el rol de Empresa 2, debe ser bloqueado con 403 Forbidden
        $responseEdit = $this->actingAs($this->adminEmpresa1)->get(route('roles.edit', $roleE2->id));
        $responseEdit->assertStatus(403);

        // 3. Si el Admin de Empresa 1 intenta eliminar el rol de Empresa 2, debe ser bloqueado con 403 Forbidden
        $responseDelete = $this->actingAs($this->adminEmpresa1)->delete(route('roles.destroy', $roleE2->id));
        $responseDelete->assertStatus(403);
    }

    public function test_admin_puede_crear_usuario_y_asignarle_rol_de_su_empresa(): void
    {
        Permission::firstOrCreate(['name' => 'ver vehiculos', 'guard_name' => 'web']);

        $roleName = 'e' . $this->empresa1->id . '_OPERADOR_PATIO';
        $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
        $role->givePermissionTo('ver vehiculos');

        // Crear usuario desde el panel
        $response = $this->actingAs($this->adminEmpresa1)->post(route('users.store'), [
            'name' => 'Operador Juan',
            'email' => 'juan_patio@transjunin.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => $roleName,
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::where('email', 'juan_patio@transjunin.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($this->empresa1->id, $user->empresa_id);
        $this->assertTrue($user->hasRole($roleName));
        $this->assertTrue($user->hasPermissionTo('ver vehiculos'));
    }

    public function test_sistema_bloquea_eliminar_un_rol_que_tiene_usuarios_asignados(): void
    {
        $roleName = 'e' . $this->empresa1->id . '_CAJERO_NOCHE';
        $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);

        $cajero = User::create([
            'name' => 'Cajero Nocturno',
            'email' => 'cajero_noche@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa1->id,
            'activo' => true,
        ]);
        $cajero->assignRole($role);

        $response = $this->actingAs($this->adminEmpresa1)->delete(route('roles.destroy', $role->id));

        $response->assertRedirect(route('roles.index'));
        $response->assertSessionHas('error', 'No se puede eliminar un rol que tiene usuarios asignados.');

        // El rol sigue existiendo
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_admin_puede_eliminar_rol_si_no_tiene_usuarios_asignados(): void
    {
        $roleName = 'e' . $this->empresa1->id . '_ROL_TEMPORAL';
        $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);

        $response = $this->actingAs($this->adminEmpresa1)->delete(route('roles.destroy', $role->id));

        $response->assertRedirect(route('roles.index'));
        $response->assertSessionHas('success', 'Rol eliminado correctamente.');

        // El rol fue eliminado
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}
