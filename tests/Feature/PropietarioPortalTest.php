<?php

namespace Tests\Feature;

use App\Models\Conductor;
use App\Models\Empresa;
use App\Models\Propietario;
use App\Models\Ruta;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Vuelta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PropietarioPortalTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $admin;
    private Propietario $propietario;
    private Vehiculo $vehiculo;
    private Conductor $conductor;
    private Ruta $ruta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::create([
            'nombre'         => 'Empresa Transportes Test SAC',
            'ruc'            => '20123456789',
            'plan'           => 'enterprise',
            'tributo_diario' => 24.00,
            'activa'         => true,
        ]);

        $roleAdmin = Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'propietario', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'conductor', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name'       => 'Administrador Sistema',
            'email'      => 'admin@transportestest.com',
            'password'   => bcrypt('admin123'),
            'empresa_id' => $this->empresa->id,
            'activo'     => true,
        ]);
        $this->admin->assignRole($roleAdmin);

        $this->propietario = Propietario::create([
            'empresa_id'           => $this->empresa->id,
            'nombre'               => 'Carlos',
            'apellidos'            => 'Mendoza',
            'dni'                  => '12345678',
            'telefono'             => '987654321',
            'tipo_persona'         => 'personal_normal',
            'monto_inicial'        => 300.00,
            'fecha_monto_inicial'  => '2026-01-10',
            'cuota_1'              => 100.00,
            'fecha_cuota_1'        => '2026-02-10',
            'cuota_2'              => 100.00,
            'fecha_cuota_2'        => '2026-03-10',
            'cuota_3'              => 100.00,
            'fecha_cuota_3'        => '2026-04-10',
            'activo'               => true,
            'primer_ingreso'       => true,
        ]);

        $this->conductor = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre'     => 'Raúl',
            'apellidos'  => 'Gómez',
            'dni'        => '87654321',
            'estado'     => 'activo',
        ]);

        $this->vehiculo = Vehiculo::create([
            'empresa_id'     => $this->empresa->id,
            'propietario_id' => $this->propietario->id,
            'conductor_id'   => $this->conductor->id,
            'placa'          => 'ABC-123',
            'numero_flota'   => 15,
            'marca'          => 'Toyota',
            'modelo'         => 'Hiace',
            'estado'         => 'activo',
        ]);

        $this->ruta = Ruta::create([
            'empresa_id' => $this->empresa->id,
            'nombre'     => 'Ruta Troncal Central',
            'origen'     => 'Terminal Norte',
            'destino'    => 'Terminal Sur',
            'estado'     => 'activa',
        ]);
    }

    public function test_admin_puede_generar_acceso_con_dni_para_el_propietario(): void
    {
        $response = $this->actingAs($this->admin)->post(route('propietarios.acceso.store', $this->propietario->id));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'empresa_id'     => $this->empresa->id,
            'propietario_id' => $this->propietario->id,
            'email'          => '12345678',
            'activo'         => true,
        ]);

        $user = User::where('propietario_id', $this->propietario->id)->first();
        $this->assertTrue($user->hasRole('propietario'));
    }

    public function test_propietario_primer_ingreso_es_forzado_a_cambiar_contraseña(): void
    {
        // Generar acceso
        $this->actingAs($this->admin)->post(route('propietarios.acceso.store', $this->propietario->id));
        $propietarioUser = User::where('propietario_id', $this->propietario->id)->first();

        // Intento de entrar a dashboard -> debe redirigir a cambiar-password
        $response = $this->actingAs($propietarioUser)->get(route('propietario.dashboard'));
        $response->assertRedirect(route('propietario.cambiar-password'));

        // Propietario cambia contraseña exitosamente
        $changePassResponse = $this->actingAs($propietarioUser)->post(route('propietario.cambiar-password.store'), [
            'password'              => 'NuevaClaveSegura123',
            'password_confirmation' => 'NuevaClaveSegura123',
        ]);

        $changePassResponse->assertRedirect(route('propietario.dashboard'));
        $this->propietario->refresh();
        $this->assertFalse((bool)$this->propietario->primer_ingreso);

        // Ahora entra directamente al dashboard
        $dashboardResponse = $this->actingAs($propietarioUser)->get(route('propietario.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Carlos Mendoza');
        $dashboardResponse->assertSee('ABC-123');
    }

    public function test_propietario_inicia_sesion_desde_formulario_login_con_dni(): void
    {
        // 1. Crear acceso
        $this->actingAs($this->admin)->post(route('propietarios.acceso.store', $this->propietario->id));
        auth()->logout();

        // 2. Propietario inicia sesión con DNI en el formulario de login público
        $response = $this->post('/login', [
            'email'    => '12345678',
            'password' => '12345678',
        ]);

        $response->assertSessionHasNoErrors();
        // Redirige al panel del propietario (y luego forzar cambio si primer ingreso)
        $response->assertRedirect(route('propietario.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_aislamiento_y_seguridad_de_rutas_para_el_propietario(): void
    {
        $this->propietario->update(['primer_ingreso' => false]);
        $user = User::create([
            'empresa_id'     => $this->empresa->id,
            'propietario_id' => $this->propietario->id,
            'name'           => 'Carlos Mendoza',
            'email'          => '12345678',
            'password'       => Hash::make('password'),
            'activo'         => true,
        ]);
        $rolePropietario = Role::findByName('propietario', 'web');
        $user->assignRole($rolePropietario);

        // Propietario intentando entrar al panel conductor -> Redirige a propietario.dashboard
        $respConductor = $this->actingAs($user)->get(route('conductor.dashboard'));
        $respConductor->assertRedirect(route('propietario.dashboard'));

        // Propietario intentando entrar al panel admin -> Redirige a propietario.dashboard
        $respAdmin = $this->actingAs($user)->get(route('dashboard'));
        $respAdmin->assertRedirect(route('propietario.dashboard'));
    }

    public function test_propietario_puede_ver_vueltas_metricas_y_monto_de_ingreso(): void
    {
        $this->propietario->update(['primer_ingreso' => false]);
        $user = User::create([
            'empresa_id'     => $this->empresa->id,
            'propietario_id' => $this->propietario->id,
            'name'           => 'Carlos Mendoza',
            'email'          => '12345678',
            'password'       => Hash::make('password'),
            'activo'         => true,
        ]);
        $rolePropietario = Role::findByName('propietario', 'web');
        $user->assignRole($rolePropietario);

        // Registrar una vuelta para su carro
        Vuelta::create([
            'empresa_id'    => $this->empresa->id,
            'vehiculo_id'   => $this->vehiculo->id,
            'conductor_id'  => $this->conductor->id,
            'ruta_id'       => $this->ruta->id,
            'fecha'         => today(),
            'numero_vuelta' => 1,
            'hora_salida'   => '07:00:00',
            'hora_llegada'  => '08:00:00',
            'estado'        => 'completada',
        ]);

        // Ver módulo de vueltas
        $respVueltas = $this->actingAs($user)->get(route('propietario.vueltas'));
        $respVueltas->assertStatus(200);
        $respVueltas->assertSee('Vueltas Flota Mes');
        $respVueltas->assertSee('ABC-123');

        // Ver módulo de Mi Flota con Monto de Ingreso y 3 cuotas
        $respDatos = $this->actingAs($user)->get(route('propietario.datos'));
        $respDatos->assertStatus(200);
        $respDatos->assertSee('Monto de Ingreso');
        $respDatos->assertSee('Monto Inicial');
        $respDatos->assertSee('Cuota 1');
        $respDatos->assertSee('Cuota 2');
        $respDatos->assertSee('Cuota 3');
    }

    public function test_actualizacion_de_cuotas_en_admin_se_refleja_en_mi_flota(): void
    {
        $this->propietario->update(['primer_ingreso' => false]);
        $user = User::create([
            'empresa_id'     => $this->empresa->id,
            'propietario_id' => $this->propietario->id,
            'name'           => 'Carlos Mendoza',
            'email'          => '12345678',
            'password'       => Hash::make('password'),
            'activo'         => true,
        ]);
        $rolePropietario = Role::findByName('propietario', 'web');
        $user->assignRole($rolePropietario);

        // 1. Admin actualiza la cuota 1 del vehículo a S/. 250.00 pagada hoy
        $this->actingAs($this->admin)->put(route('propietarios.update', $this->propietario->id), [
            'nombre'        => $this->propietario->nombre,
            'apellidos'     => $this->propietario->apellidos,
            'tipo_persona'  => 'personal_normal',
            'vehiculos'     => [
                $this->vehiculo->id => [
                    'monto_inicial'       => 300.00,
                    'fecha_monto_inicial' => '2026-01-10',
                    'cuota_1'             => 250.00,
                    'fecha_cuota_1'       => '2026-09-03',
                    'cuota_2'             => 0.00,
                    'cuota_3'             => 0.00,
                ]
            ]
        ]);

        $this->vehiculo->refresh();
        $this->assertEquals(250.00, $this->vehiculo->cuota_1);

        // 2. Propietario consulta Mi Flota y ve reflejado los S/. 250.00 y la fecha
        $respDatos = $this->actingAs($user)->get(route('propietario.datos'));
        $respDatos->assertStatus(200);
        $respDatos->assertSee('250.00');
        $respDatos->assertSee('03/09/2026');
    }

    public function test_propietario_puede_actualizar_soat_y_revision_tecnica_de_su_vehiculo(): void
    {
        $this->propietario->update(['primer_ingreso' => false]);
        $user = User::create([
            'empresa_id'     => $this->empresa->id,
            'propietario_id' => $this->propietario->id,
            'name'           => 'Carlos Mendoza',
            'email'          => '12345678',
            'password'       => Hash::make('password'),
            'activo'         => true,
        ]);
        $rolePropietario = Role::findByName('propietario', 'web');
        $user->assignRole($rolePropietario);

        $response = $this->actingAs($user)->put(route('propietario.vehiculos.update-documentos', $this->vehiculo->id), [
            'soat_vence'        => '2028-01-15',
            'rev_tecnica_vence' => '2028-02-28',
        ]);

        $response->assertRedirect(route('propietario.datos'));
        $response->assertSessionHas('success');

        $this->vehiculo->refresh();
        $this->assertEquals('2028-01-15', $this->vehiculo->soat_vence->format('Y-m-d'));
        $this->assertEquals('2028-02-28', $this->vehiculo->rev_tecnica_vence->format('Y-m-d'));
    }
}
