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
}
