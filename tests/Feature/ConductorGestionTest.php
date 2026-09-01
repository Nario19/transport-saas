<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Vehiculo;
use App\Models\Conductor;
use Spatie\Permission\Models\Role;

class ConductorGestionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
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

        $this->admin = User::create([
            'name' => 'Admin RRHH',
            'email' => 'rrhh@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa->id,
            'activo' => true,
        ]);
        $this->admin->assignRole($role);
    }

    public function test_admin_puede_registrar_un_nuevo_conductor(): void
    {
        $response = $this->actingAs($this->admin)->post(route('conductores.store'), [
            'nombre' => 'Mateo',
            'apellidos' => 'Salazar',
            'dni' => '70809010',
            'telefono' => '955443322',
            'tipo_licencia' => 'A-IIB',
            'licencia_vence' => '2028-05-15',
            'estado' => 'activo',
        ]);

        $response->assertRedirect(route('conductores.index'));
        $this->assertDatabaseHas('conductores', [
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Mateo',
            'dni' => '70809010',
            'tipo_licencia' => 'A-IIB',
        ]);
    }

    public function test_sistema_bloquea_crear_acceso_app_si_conductor_no_tiene_vehiculo_asignado(): void
    {
        $conductor = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sin',
            'apellidos' => 'Auto',
            'dni' => '88990011',
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->admin)->post(route('conductores.acceso.store', $conductor->id));

        $response->assertSessionHas('error', 'No se puede crear el acceso: El conductor no tiene un vehículo asignado.');
        $this->assertFalse($conductor->user()->exists());
    }

    public function test_admin_puede_crear_acceso_movil_para_conductor_con_vehiculo(): void
    {
        $conductor = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Jorge',
            'apellidos' => 'Rios',
            'dni' => '33445566',
            'estado' => 'activo',
        ]);

        Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $conductor->id,
            'placa' => 'W3X-123',
            'numero_flota' => 88,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->admin)->post(route('conductores.acceso.store', $conductor->id));

        $response->assertSessionHasNoErrors();
        $this->assertTrue($conductor->user()->exists());

        // El usuario se crea con el identificador de la placa
        $this->assertDatabaseHas('users', [
            'conductor_id' => $conductor->id,
            'email' => 'W3X123',
        ]);
    }
}
