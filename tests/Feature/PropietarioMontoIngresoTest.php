<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Propietario;
use App\Models\Vehiculo;
use Spatie\Permission\Models\Role;

class PropietarioMontoIngresoTest extends TestCase
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
            'name' => 'Admin Test',
            'email' => 'admin@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa->id,
            'activo' => true,
        ]);
        $this->admin->assignRole($role);
    }

    public function test_socio_de_la_empresa_esta_exonerado_de_monto_de_ingreso(): void
    {
        $socio = Propietario::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Carlos',
            'apellidos' => 'Huaman',
            'dni' => '12345678',
            'tipo_persona' => 'socio',
            'activo' => true,
        ]);

        // Un socio debe tener 0 deuda y total 0
        $this->assertTrue($socio->es_socio);
        $this->assertEquals(0, $socio->monto_ingreso_total);
        $this->assertEquals(0, $socio->monto_ingreso_deuda);
        $this->assertEquals('EXONERADO (SOCIO)', $socio->estado_ingreso);
    }

    public function test_persona_normal_calcula_deuda_segun_cuotas_abonadas(): void
    {
        $propietario = Propietario::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Mario',
            'apellidos' => 'Perez',
            'dni' => '87654321',
            'tipo_persona' => 'personal_normal',
            'monto_inicial' => 200.00,
            'fecha_monto_inicial' => '2026-08-30',
            'cuota_1' => 100.00,
            'fecha_cuota_1' => '2026-08-31',
            'cuota_2' => 0.00,
            'cuota_3' => 0.00,
            'activo' => true,
        ]);

        // Total abonado: 300.00, Deuda: 300.00
        $this->assertFalse($propietario->es_socio);
        $this->assertEquals(300.00, $propietario->monto_ingreso_total);
        $this->assertEquals(300.00, $propietario->monto_ingreso_deuda);
        $this->assertEquals('DEUDA', $propietario->estado_ingreso);
    }

    public function test_persona_normal_con_multiples_vehiculos_acumula_deuda_independiente_por_unidad(): void
    {
        $propietario = Propietario::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Roberto',
            'apellidos' => 'Gomez',
            'dni' => '44556677',
            'tipo_persona' => 'personal_normal',
            'monto_inicial' => 600.00,
            'activo' => true,
        ]);

        // Vehículo 1 (Pagado completo)
        $v1 = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'propietario_id' => $propietario->id,
            'placa' => 'W1A-101',
            'numero_flota' => 1,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'anio' => 2022,
            'monto_inicial' => 600.00,
            'estado' => 'activo',
        ]);

        // Vehículo 2 (Nuevo vehículo asignado con cuotas en 0.00)
        $v2 = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'propietario_id' => $propietario->id,
            'placa' => 'W2B-202',
            'numero_flota' => 2,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'anio' => 2023,
            'monto_inicial' => 0.00,
            'estado' => 'activo',
        ]);

        $propietario->refresh();

        // Vehículo 1: deuda 0, Vehículo 2: deuda 600
        $this->assertEquals(0, $v1->monto_ingreso_deuda);
        $this->assertEquals(600.00, $v2->monto_ingreso_deuda);

        // Deuda acumulada total del propietario = 600.00
        $this->assertEquals(600.00, $propietario->monto_ingreso_total);
        $this->assertEquals(600.00, $propietario->monto_ingreso_deuda);
    }

    public function test_vista_show_de_propietario_responde_correctamente(): void
    {
        $propietario = Propietario::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Juan',
            'apellidos' => 'Quispe',
            'dni' => '11223344',
            'tipo_persona' => 'personal_normal',
            'monto_inicial' => 600.00,
            'activo' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('propietarios.show', $propietario->id));

        $response->assertStatus(200);
        $response->assertSee('Juan Quispe');
        $response->assertSee('Control de Monto de Ingreso');
        $response->assertDontSee('Obligación: S/. 1,800.00');
    }
}
