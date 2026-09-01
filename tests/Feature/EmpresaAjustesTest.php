<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Vehiculo;
use App\Models\Tributo;
use Spatie\Permission\Models\Role;

class EmpresaAjustesTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::create([
            'nombre' => 'Transportes Junín SAC',
            'ruc' => '20123456789',
            'plan' => 'enterprise',
            'tributo_diario' => 24.00,
            'activa' => true,
        ]);

        $role = Role::findByName('SUPER_ADMIN', 'web');

        $this->superadmin = User::firstOrCreate(
            ['email' => 'master_superadmin@test.com'],
            [
                'name' => 'Master Super Admin',
                'password' => bcrypt('password123'),
                'activo' => true,
            ]
        );
        $this->superadmin->assignRole($role);
    }

    public function test_superadmin_puede_listar_empresas_en_panel_global(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('superadmin.empresas.index'));

        $response->assertStatus(200);
        $response->assertSee('Transportes Junín SAC');
        $response->assertSee('20123456789');
    }

    public function test_superadmin_puede_crear_nueva_empresa_de_transportes(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('superadmin.empresas.store'), [
            'nombre' => 'Empresa Huancayo Express SAC',
            'ruc' => '20987654321',
            'telefono' => '988776655',
            'plan' => 'pro',
            'tributo_diario' => 30.00,
            'activa' => 1,
            'admin_name' => 'Admin Empresa Nueva',
            'admin_email' => 'admin_nueva@empresa.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('superadmin.empresas.index'));
        $this->assertDatabaseHas('empresas', [
            'ruc' => '20987654321',
            'tributo_diario' => 30.00,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'admin_nueva@empresa.com',
        ]);
    }

    public function test_superadmin_puede_desactivar_y_activar_empresa(): void
    {
        $response = $this->actingAs($this->superadmin)->patch(route('superadmin.empresas.toggle', $this->empresa->id));

        $response->assertSessionHasNoErrors();
        $this->empresa->refresh();

        $this->assertFalse((bool) $this->empresa->activa);
    }

    public function test_empresa_con_tributo_personalizado_de_5_soles_genera_tributos_exactos_de_5_soles(): void
    {
        // 1. Crear empresa con tributo diario de 5.00
        $empresa5 = Empresa::create([
            'nombre' => 'Empresa Cinco Soles SAC',
            'ruc' => '20555555555',
            'plan' => 'basico',
            'tributo_diario' => 5.00,
            'activa' => true,
        ]);

        $vehiculo = Vehiculo::create([
            'empresa_id' => $empresa5->id,
            'placa' => 'CIN-500',
            'numero_flota' => 50,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'estado' => 'activo',
        ]);

        // 2. Generar tributos diarios
        Tributo::ensureGenerados($empresa5->id);

        // 3. El monto generado debe ser estrictamente 5.00 y NUNCA 24.00
        $tributo = Tributo::where('vehiculo_id', $vehiculo->id)->first();
        $this->assertNotNull($tributo);
        $this->assertEquals(5.00, (float) $tributo->monto);
        $this->assertNotEquals(24.00, (float) $tributo->monto);
    }
}
