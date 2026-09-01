<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Vehiculo;
use App\Models\Conductor;
use App\Models\Sancion;
use Spatie\Permission\Models\Role;

class SancionOperacionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Empresa $empresa;
    private Vehiculo $vehiculo;
    private Conductor $conductor;

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
            'name' => 'Fiscalizador / Admin',
            'email' => 'admin@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa->id,
            'activo' => true,
        ]);
        $this->admin->assignRole($role);

        $this->conductor = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Raul',
            'apellidos' => 'Vargas',
            'dni' => '40506070',
            'primer_ingreso' => false,
            'estado' => 'activo',
        ]);

        $this->vehiculo = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $this->conductor->id,
            'placa' => 'W3C-888',
            'numero_flota' => 8,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'estado' => 'activo',
        ]);
    }

    public function test_admin_puede_registrar_sancion_y_marcar_como_pendiente(): void
    {
        $response = $this->actingAs($this->admin)->post(route('sanciones.store'), [
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'fecha' => today()->toDateString(),
            'monto' => 50.00,
            'motivo' => 'Exceso de velocidad en tramo urbano',
        ]);

        $response->assertRedirect(route('sanciones.index'));
        $this->assertDatabaseHas('sanciones', [
            'vehiculo_id' => $this->vehiculo->id,
            'monto' => 50.00,
            'estado' => 'pendiente',
        ]);
    }

    public function test_admin_puede_cobrar_sancion_manualmente(): void
    {
        $sancion = Sancion::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'fecha' => today()->toDateString(),
            'monto' => 50.00,
            'motivo' => 'Falta de uniforme reglamentario',
            'estado' => 'pendiente',
            'registrado_por' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('sanciones.pagar', $sancion->id), [
            'metodo_pago' => 'efectivo',
        ]);

        $response->assertSessionHasNoErrors();
        $sancion->refresh();

        $this->assertEquals('pagado', $sancion->estado);
        $this->assertEquals('efectivo', $sancion->metodo_pago);
        $this->assertEquals($this->admin->id, $sancion->cobrado_por);
    }
}
