<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_raiz_redirige_a_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_registro_publico_esta_bloqueado_y_redirige_a_login(): void
    {
        $response = $this->get('/register');
        $response->assertRedirect(route('login'));
    }

    public function test_superadmin_puede_iniciar_sesion_y_ver_dashboard(): void
    {
        $role = Role::findByName('SUPER_ADMIN', 'web');

        $user = User::create([
            'name' => 'Super Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'activo' => true,
        ]);
        $user->assignRole($role);

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_usuario_no_autenticado_es_redirigido_al_intentar_entrar_al_panel(): void
    {
        $response = $this->get('/admin/vehiculos');
        $response->assertRedirect('/login');
    }
}
