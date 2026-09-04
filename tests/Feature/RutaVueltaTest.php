<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Vehiculo;
use App\Models\Conductor;
use App\Models\Ruta;
use App\Models\Vuelta;
use Spatie\Permission\Models\Role;

class RutaVueltaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Empresa $empresa;
    private Ruta $ruta;
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
            'name' => 'Despachador Principal',
            'email' => 'despacho@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa->id,
            'activo' => true,
        ]);
        $this->admin->assignRole($role);

        $this->ruta = Ruta::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Ruta Troncal Huancayo - Jauja',
            'origen' => 'Huancayo Terminal',
            'destino' => 'Jauja Plaza',
            'tiempo_estimado_minutos' => 60,
            'estado' => 'activa',
        ]);

        $this->conductor = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Enrique',
            'apellidos' => 'Flores',
            'dni' => '44332211',
            'primer_ingreso' => false,
            'requiere_facial' => false,
            'estado' => 'activo',
        ]);

        $this->vehiculo = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $this->conductor->id,
            'placa' => 'W1A-500',
            'numero_flota' => 5,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'estado' => 'activo',
        ]);
    }

    public function test_admin_puede_registrar_vuelta_manual_en_el_sistema(): void
    {
        $response = $this->actingAs($this->admin)->post(route('vueltas.store'), [
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'ruta_id' => $this->ruta->id,
            'fecha' => today()->toDateString(),
            'numero_vuelta' => 1,
            'hora_salida' => '07:00:00',
            'hora_llegada' => '08:00:00',
            'estado' => 'completada',
        ]);

        $response->assertRedirect(route('vueltas.index'));
        $this->assertDatabaseHas('vueltas', [
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'numero_vuelta' => 1,
            'estado' => 'completada',
        ]);
    }

    public function test_sistema_evita_duplicar_el_mismo_numero_de_vuelta_en_el_mismo_dia(): void
    {
        Vuelta::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'ruta_id' => $this->ruta->id,
            'fecha' => today()->toDateString(),
            'numero_vuelta' => 1,
            'hora_salida' => '07:00:00',
            'hora_llegada' => '08:00:00',
            'estado' => 'completada',
        ]);

        $response = $this->actingAs($this->admin)->post(route('vueltas.store'), [
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'ruta_id' => $this->ruta->id,
            'fecha' => today()->toDateString(),
            'numero_vuelta' => 1,
            'hora_salida' => '08:30:00',
            'estado' => 'completada',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_admin_puede_marcar_como_completada_una_vuelta_activa(): void
    {
        $vuelta = Vuelta::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'ruta_id' => $this->ruta->id,
            'fecha' => today()->toDateString(),
            'numero_vuelta' => 2,
            'hora_salida' => '09:00:00',
            'estado' => 'activa',
        ]);

        // POST route for completar
        $response = $this->actingAs($this->admin)->post(route('vueltas.completar', $vuelta->id));

        $response->assertSessionHasNoErrors();
        $vuelta->refresh();

        $this->assertEquals('completada', $vuelta->estado);
        $this->assertNotNull($vuelta->hora_llegada);
    }

    public function test_colores_de_estado_segun_paraderos_recorridos(): void
    {
        $pA = \App\Models\RutaParadero::create(['ruta_id' => $this->ruta->id, 'nombre' => 'Paradero A', 'tipo' => 'origen', 'orden' => 1]);
        $pB = \App\Models\RutaParadero::create(['ruta_id' => $this->ruta->id, 'nombre' => 'Paradero B', 'tipo' => 'intermedio', 'orden' => 2]);
        $pC = \App\Models\RutaParadero::create(['ruta_id' => $this->ruta->id, 'nombre' => 'Paradero C', 'tipo' => 'intermedio', 'orden' => 3]);
        $pD = \App\Models\RutaParadero::create(['ruta_id' => $this->ruta->id, 'nombre' => 'Paradero D', 'tipo' => 'destino', 'orden' => 4]);
        $pE = \App\Models\RutaParadero::create(['ruta_id' => $this->ruta->id, 'nombre' => 'Paradero E', 'tipo' => 'destino', 'orden' => 5]);

        // 1. Caso VERDE: Origen a Destino (A -> D)
        $vVerde = Vuelta::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'ruta_id' => $this->ruta->id,
            'paradero_salida_id' => $pA->id,
            'paradero_llegada_id' => $pD->id,
            'fecha' => today(),
            'numero_vuelta' => 10,
            'hora_salida' => '07:00:00',
            'hora_llegada' => '08:00:00',
            'estado' => 'completada',
        ]);
        $this->assertEquals('verde', $vVerde->badge_estado['categoria']);

        // 2. Caso ROJO: 1 solo salto consecutivo (A -> B, B -> C, C -> D)
        $vRojo = Vuelta::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'ruta_id' => $this->ruta->id,
            'paradero_salida_id' => $pA->id,
            'paradero_llegada_id' => $pB->id,
            'fecha' => today(),
            'numero_vuelta' => 11,
            'hora_salida' => '08:10:00',
            'hora_llegada' => '08:30:00',
            'estado' => 'completada',
        ]);
        $this->assertEquals('rojo', $vRojo->badge_estado['categoria']);

        // 3. Caso NARANJA: 2 o más saltos intermedios (A -> C, B -> D, B -> E)
        $vNaranja = Vuelta::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'ruta_id' => $this->ruta->id,
            'paradero_salida_id' => $pA->id,
            'paradero_llegada_id' => $pC->id,
            'fecha' => today(),
            'numero_vuelta' => 12,
            'hora_salida' => '08:40:00',
            'hora_llegada' => '09:10:00',
            'estado' => 'completada',
        ]);
        $this->assertEquals('naranja', $vNaranja->badge_estado['categoria']);
    }

    public function test_sistema_bloquea_iniciar_o_terminar_vuelta_con_ubicacion_simulada(): void
    {
        $roleConductor = Role::firstOrCreate(['name' => 'conductor', 'guard_name' => 'web']);
        $perms = ['conductor.dashboard', 'conductor.tributos', 'conductor.vueltas', 'conductor.sanciones', 'conductor.perfil'];
        foreach ($perms as $p) {
            $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            $roleConductor->givePermissionTo($perm);
        }

        $userConductor = User::create([
            'name' => 'Chofer Test',
            'email' => 'chofer@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $this->conductor->id,
            'activo' => true,
            'primer_ingreso' => false,
        ]);
        $userConductor->assignRole($roleConductor);

        $pA = \App\Models\RutaParadero::create([
            'ruta_id' => $this->ruta->id,
            'nombre' => 'Terminal Inicio',
            'tipo' => 'origen',
            'orden' => 1,
            'latitud_a' => -12.065,
            'longitud_a' => -75.205,
            'latitud_b' => -12.066,
            'longitud_b' => -75.206,
            'tolerancia' => 50,
        ]);

        $pB = \App\Models\RutaParadero::create([
            'ruta_id' => $this->ruta->id,
            'nombre' => 'Terminal Destino',
            'tipo' => 'destino',
            'orden' => 2,
            'latitud_a' => -11.775,
            'longitud_a' => -75.495,
            'latitud_b' => -11.776,
            'longitud_b' => -75.496,
            'tolerancia' => 50,
        ]);

        // 1. Intento de iniciar con Fake GPS (is_mock = true)
        $respIniciar = $this->actingAs($userConductor)->postJson(route('conductor.vuelta.iniciar.post'), [
            'ruta_id' => $this->ruta->id,
            'ruta_paradero_id' => $pA->id,
            'latitud' => -12.065,
            'longitud' => -75.205,
            'verificado_rostro' => true,
            'is_mock' => true,
        ]);

        $respIniciar->assertStatus(422)
            ->assertJson(['ok' => false, 'error' => 'Ubicación simulada detectada. No se permite iniciar la vuelta con aplicaciones de GPS simulado.']);

        // Iniciar correctamente sin mock
        $this->actingAs($userConductor)->postJson(route('conductor.vuelta.iniciar.post'), [
            'ruta_id' => $this->ruta->id,
            'ruta_paradero_id' => $pA->id,
            'latitud' => -12.065,
            'longitud' => -75.205,
            'verificado_rostro' => true,
            'is_mock' => false,
        ])->assertOk();

        // 2. Intento de terminar con Fake GPS (is_mock = true)
        $respTerminar = $this->actingAs($userConductor)->postJson(route('conductor.vuelta.terminar'), [
            'paradero_llegada_id' => $pB->id,
            'latitud' => -11.775,
            'longitud' => -75.495,
            'is_mock' => true,
        ]);

        $respTerminar->assertStatus(422)
            ->assertJson(['ok' => false, 'error' => 'Ubicación simulada detectada. No se permite finalizar la vuelta con aplicaciones de GPS simulado.']);
    }

    public function test_sistema_bloquea_terminar_vuelta_por_salto_anomalo_de_velocidad(): void
    {
        $roleConductor = Role::firstOrCreate(['name' => 'conductor', 'guard_name' => 'web']);
        $perms = ['conductor.dashboard', 'conductor.tributos', 'conductor.vueltas', 'conductor.sanciones', 'conductor.perfil'];
        foreach ($perms as $p) {
            $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            $roleConductor->givePermissionTo($perm);
        }

        $userConductor = User::create([
            'name' => 'Chofer Speed',
            'email' => 'speed@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $this->conductor->id,
            'activo' => true,
            'primer_ingreso' => false,
        ]);
        $userConductor->assignRole($roleConductor);

        $pA = \App\Models\RutaParadero::create([
            'ruta_id' => $this->ruta->id,
            'nombre' => 'Paradero Inicio',
            'tipo' => 'origen',
            'orden' => 1,
            'latitud_a' => -12.065,
            'longitud_a' => -75.205,
            'latitud_b' => -12.066,
            'longitud_b' => -75.206,
            'tolerancia' => 50,
        ]);

        $pB = \App\Models\RutaParadero::create([
            'ruta_id' => $this->ruta->id,
            'nombre' => 'Paradero Lejano',
            'tipo' => 'destino',
            'orden' => 2,
            'latitud_a' => -11.775,
            'longitud_a' => -75.495,
            'latitud_b' => -11.776,
            'longitud_b' => -75.496,
            'tolerancia' => 50,
        ]);

        $vuelta = Vuelta::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'ruta_id' => $this->ruta->id,
            'paradero_salida_id' => $pA->id,
            'fecha' => today(),
            'numero_vuelta' => 5,
            'hora_salida' => now()->subMinutes(10)->format('H:i:s'),
            'estado' => 'activa',
            'lat_actual' => -12.065, // Huancayo
            'lng_actual' => -75.205,
        ]);
        // Simulamos que la última posición fue registrada hace 10 segundos
        \App\Models\Vuelta::withoutTimestamps(function () use ($vuelta) {
            $vuelta->updated_at = now()->subSeconds(10);
            $vuelta->saveQuietly();
        });

        // Intento de finalizar en Jauja (a más de 40 km) en sólo 10 segundos
        $response = $this->actingAs($userConductor)->postJson(route('conductor.vuelta.terminar'), [
            'paradero_llegada_id' => $pB->id,
            'latitud' => -11.775,
            'longitud' => -75.495,
            'is_mock' => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['ok', 'error']);
        $this->assertStringContainsString('Salto anómalo de ubicación', $response->json('error'));
    }
}
