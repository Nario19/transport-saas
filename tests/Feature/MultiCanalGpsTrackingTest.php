<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Empresa;
use App\Models\Vehiculo;
use App\Models\Conductor;
use App\Models\Ruta;
use App\Models\Vuelta;
use App\Models\User;
use Spatie\Permission\Models\Role;

class MultiCanalGpsTrackingTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $admin;
    private User $conductorAndroidUser;
    private Vehiculo $vehiculoAndroid;
    private Vehiculo $vehiculoIphone;
    private Vuelta $vueltaAndroid;
    private Vuelta $vueltaIphone;

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

        $roleAdmin = Role::firstOrCreate(['name' => 'e' . $this->empresa->id . '_ADMIN', 'guard_name' => 'web']);
        $this->admin = User::create([
            'empresa_id' => $this->empresa->id,
            'name' => 'Admin Central',
            'email' => 'admin@transjunin.com',
            'password' => bcrypt('password123'),
            'activo' => true,
        ]);
        $this->admin->assignRole($roleAdmin);

        $ruta = Ruta::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Ruta Troncal',
            'origen' => 'Terminal Norte',
            'destino' => 'Terminal Sur',
            'estado' => 'activa',
        ]);

        // 1. Conductor y Vehículo Android (Flota #10 - W1A-100)
        $conductorAndroid = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
            'dni' => '11223344',
            'primer_ingreso' => false,
            'estado' => 'activo',
        ]);
        $this->vehiculoAndroid = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $conductorAndroid->id,
            'placa' => 'W1A-100',
            'numero_flota' => 10,
            'estado' => 'activo',
        ]);
        $roleConductor = Role::firstOrCreate(['name' => 'conductor', 'guard_name' => 'web']);
        $this->conductorAndroidUser = User::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $conductorAndroid->id,
            'name' => 'Juan Pérez Chofer',
            'email' => 'W1A100',
            'password' => bcrypt('password123'),
            'activo' => true,
        ]);
        $this->conductorAndroidUser->assignRole($roleConductor);

        $this->vueltaAndroid = Vuelta::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculoAndroid->id,
            'conductor_id' => $conductorAndroid->id,
            'ruta_id' => $ruta->id,
            'fecha' => today(),
            'numero_vuelta' => 1,
            'hora_salida' => '08:00:00',
            'latitud' => -12.060000,
            'longitud' => -75.200000,
            'estado' => 'activa',
            'created_by' => $this->conductorAndroidUser->id,
        ]);

        // 2. Conductor y Vehículo iPhone (Flota #20 - W2B-200)
        $conductorIphone = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Luis',
            'apellidos' => 'Rojas',
            'dni' => '55667788',
            'primer_ingreso' => false,
            'estado' => 'activo',
        ]);
        $this->vehiculoIphone = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $conductorIphone->id,
            'placa' => 'W2B-200',
            'numero_flota' => 20,
            'estado' => 'activo',
        ]);

        $this->vueltaIphone = Vuelta::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculoIphone->id,
            'conductor_id' => $conductorIphone->id,
            'ruta_id' => $ruta->id,
            'fecha' => today(),
            'numero_vuelta' => 1,
            'hora_salida' => '08:05:00',
            'latitud' => -12.062000,
            'longitud' => -75.202000,
            'estado' => 'activa',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_ambos_canales_android_y_iphone_actualizan_el_mapa_en_vivo_simultaneamente(): void
    {
        // 1. Chofer Android envía coordenadas desde la App
        $responseAndroid = $this->actingAs($this->conductorAndroidUser)->postJson(route('conductor.vuelta.ubicacion'), [
            'latitud' => -12.067890,
            'longitud' => -75.215432,
        ]);
        $responseAndroid->assertStatus(200);
        $responseAndroid->assertJsonFragment(['ok' => true]);

        // 2. Chofer iPhone envía coordenadas desde Traccar Client
        $responseIphone = $this->get('/api/gps/traccar?id=W2B-200&lat=-12.073333&lon=-75.218888&speed=40&bearing=90');
        $responseIphone->assertStatus(200);
        $this->assertEquals('OK', $responseIphone->getContent());

        // 3. El Administrador consulta las posiciones en vivo del mapa
        $responseAdmin = $this->actingAs($this->admin)->getJson(route('vueltas.api.activas'));
        $responseAdmin->assertStatus(200);

        $vueltasEnMapa = $responseAdmin->json('vueltas');
        $this->assertCount(2, $vueltasEnMapa);

        // Validar coordenadas de la unidad Android en el mapa
        $unidadAndroid = collect($vueltasEnMapa)->firstWhere('vehiculo', 'W1A-100');
        $this->assertNotNull($unidadAndroid);
        $this->assertEquals(-12.067890, (float) $unidadAndroid['lat_actual']);
        $this->assertEquals(-75.215432, (float) $unidadAndroid['lng_actual']);

        // Validar coordenadas de la unidad iPhone en el mapa
        $unidadIphone = collect($vueltasEnMapa)->firstWhere('vehiculo', 'W2B-200');
        $this->assertNotNull($unidadIphone);
        $this->assertEquals(-12.073333, (float) $unidadIphone['lat_actual']);
        $this->assertEquals(-75.218888, (float) $unidadIphone['lng_actual']);
    }
}
