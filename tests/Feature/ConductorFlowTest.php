<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Conductor;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ConductorFlowTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Conductor $conductor;
    private User $conductorUser;

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

        $role = Role::firstOrCreate(['name' => 'conductor', 'guard_name' => 'web']);
        $perms = [
            'conductor.dashboard',
            'conductor.tributos',
            'conductor.vueltas',
            'conductor.sanciones',
            'conductor.perfil',
        ];
        foreach ($perms as $p) {
            $perm = Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            $role->givePermissionTo($perm);
        }

        $this->conductor = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Pedro',
            'apellidos' => 'Castillo',
            'dni' => '77889900',
            'primer_ingreso' => false,
            'requiere_facial' => false,
            'estado' => 'activo',
        ]);

        $this->conductorUser = User::create([
            'name' => 'Pedro Castillo',
            'email' => 'conductor@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $this->conductor->id,
            'activo' => true,
        ]);
        $this->conductorUser->assignRole($role);
    }

    public function test_conductor_puede_iniciar_sesion_y_acceder_al_dashboard_de_conductor(): void
    {
        $response = $this->post('/login', [
            'email' => 'conductor@transjunin.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/conductor/dashboard');
        $this->assertAuthenticatedAs($this->conductorUser);
    }

    public function test_conductor_puede_ver_su_panel_de_vueltas_y_tributos(): void
    {
        $response = $this->actingAs($this->conductorUser)->get(route('conductor.dashboard'));
        $response->assertStatus(200);

        $responseTributos = $this->actingAs($this->conductorUser)->get(route('conductor.tributos'));
        $responseTributos->assertStatus(200);
    }

    public function test_conductor_puede_actualizar_soat_y_revision_tecnica(): void
    {
        $vehiculo = \App\Models\Vehiculo::create([
            'empresa_id'   => $this->empresa->id,
            'conductor_id' => $this->conductor->id,
            'placa'        => 'XYZ-999',
            'numero_flota' => 20,
            'marca'        => 'Toyota',
            'modelo'       => 'Hiace',
            'estado'       => 'activo',
        ]);

        $response = $this->actingAs($this->conductorUser)->put(route('conductor.perfil.update'), [
            'telefono'          => '987654321',
            'tipo_licencia'     => 'AII-B',
            'licencia_vence'    => '2027-05-20',
            'soat_vence'        => '2027-12-31',
            'rev_tecnica_vence' => '2027-11-15',
        ]);

        $response->assertRedirect(route('conductor.perfil'));
        $response->assertSessionHas('success');

        $vehiculo->refresh();
        $this->assertEquals('2027-12-31', $vehiculo->soat_vence->format('Y-m-d'));
        $this->assertEquals('2027-11-15', $vehiculo->rev_tecnica_vence->format('Y-m-d'));
    }
}
