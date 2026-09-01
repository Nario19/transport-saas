<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Vehiculo;
use App\Models\Propietario;
use App\Models\Conductor;
use Spatie\Permission\Models\Role;

class VehiculoOperacionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Empresa $empresa;
    private Propietario $propietario;

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
            'name' => 'Admin Flota',
            'email' => 'flota@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa->id,
            'activo' => true,
        ]);
        $this->admin->assignRole($role);

        $this->propietario = Propietario::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Carlos',
            'apellidos' => 'Gomez',
            'dni' => '11223344',
            'tipo_persona' => 'personal_normal',
            'activo' => true,
        ]);
    }

    public function test_admin_puede_registrar_vehiculo_con_datos_completos(): void
    {
        $response = $this->actingAs($this->admin)->post(route('vehiculos.store'), [
            'placa' => 'W1A-789',
            'numero_flota' => 15,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'color' => 'Blanco',
            'anio' => 2022,
            'estado' => 'activo',
            'propietario_id' => $this->propietario->id,
            'soat_vence' => '2027-12-31',
            'rev_tecnica_vence' => '2027-12-31',
        ]);

        $response->assertRedirect(route('vehiculos.index'));
        $this->assertDatabaseHas('vehiculos', [
            'empresa_id' => $this->empresa->id,
            'placa' => 'W1A-789',
            'numero_flota' => 15,
            'propietario_id' => $this->propietario->id,
        ]);
    }

    public function test_sistema_valida_que_la_placa_sea_unica(): void
    {
        Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'placa' => 'W1A-789',
            'numero_flota' => 1,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'anio' => 2022,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->admin)->post(route('vehiculos.store'), [
            'placa' => 'W1A-789', // Placa repetida
            'numero_flota' => 2,
            'marca' => 'Nissan',
            'modelo' => 'Urvan',
            'anio' => 2021,
            'estado' => 'activo',
        ]);

        $response->assertSessionHasErrors('placa');
    }

    public function test_sistema_valida_que_el_numero_de_flota_sea_unico_por_empresa(): void
    {
        Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'placa' => 'W1A-111',
            'numero_flota' => 50,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'anio' => 2022,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->admin)->post(route('vehiculos.store'), [
            'placa' => 'W2B-222',
            'numero_flota' => 50, // Flota repetida en la misma empresa
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'anio' => 2022,
            'estado' => 'activo',
        ]);

        $response->assertSessionHasErrors('numero_flota');
    }

    public function test_admin_puede_actualizar_datos_del_vehiculo(): void
    {
        $vehiculo = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'placa' => 'W1A-999',
            'numero_flota' => 99,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'anio' => 2022,
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->admin)->put(route('vehiculos.update', $vehiculo->id), [
            'placa' => 'W1A-999',
            'numero_flota' => 99,
            'marca' => 'Toyota',
            'modelo' => 'Commuter',
            'color' => 'Plateado',
            'anio' => 2023,
            'estado' => 'mantenimiento',
        ]);

        $response->assertRedirect(route('vehiculos.index'));
        $vehiculo->refresh();

        $this->assertEquals('Commuter', $vehiculo->modelo);
        $this->assertEquals('Plateado', $vehiculo->color);
        $this->assertEquals('mantenimiento', $vehiculo->estado);
    }
}
