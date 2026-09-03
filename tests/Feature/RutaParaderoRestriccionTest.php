<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Vehiculo;
use App\Models\Conductor;
use App\Models\Ruta;
use App\Models\RutaParadero;
use App\Models\Vuelta;
use Spatie\Permission\Models\Role;

class RutaParaderoRestriccionTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Ruta $ruta;
    private RutaParadero $pSantaRosa;
    private RutaParadero $pBeatrizLucia;
    private RutaParadero $pAncashLima;
    private RutaParadero $pIca;
    private RutaParadero $pYauris;
    private Conductor $conductor;
    private User $userConductor;
    private Vehiculo $vehiculo;

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

        // Crear Ruta con los 5 paraderos del ejemplo exacto del usuario
        $this->ruta = Ruta::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Ruta Centro - Sur',
            'origen' => 'Jr Santa Rosa',
            'destino' => 'Yauris',
            'estado' => 'activa',
        ]);

        $this->pSantaRosa = RutaParadero::create([
            'ruta_id' => $this->ruta->id,
            'nombre' => 'Jr Santa Rosa',
            'tipo' => 'origen',
            'orden' => 1,
            'latitud_a' => -12.065,
            'longitud_a' => -75.210,
        ]);

        $this->pBeatrizLucia = RutaParadero::create([
            'ruta_id' => $this->ruta->id,
            'nombre' => 'S. Beatriz - S. Lucia',
            'tipo' => 'intermedio',
            'orden' => 2,
            'latitud_a' => -12.068,
            'longitud_a' => -75.213,
        ]);

        $this->pAncashLima = RutaParadero::create([
            'ruta_id' => $this->ruta->id,
            'nombre' => 'Ancash y Lima',
            'tipo' => 'intermedio',
            'orden' => 3,
            'latitud_a' => -12.072,
            'longitud_a' => -75.217,
        ]);

        $this->pIca = RutaParadero::create([
            'ruta_id' => $this->ruta->id,
            'nombre' => 'Ica',
            'tipo' => 'destino',
            'orden' => 4,
            'latitud_a' => -12.075,
            'longitud_a' => -75.220,
        ]);

        $this->pYauris = RutaParadero::create([
            'ruta_id' => $this->ruta->id,
            'nombre' => 'Yauris',
            'tipo' => 'destino',
            'orden' => 5,
            'latitud_a' => -12.078,
            'longitud_a' => -75.223,
        ]);

        $this->conductor = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Mario',
            'apellidos' => 'Vargas',
            'dni' => '40506070',
            'primer_ingreso' => false,
            'estado' => 'activo',
        ]);

        $this->vehiculo = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $this->conductor->id,
            'placa' => 'W1A-333',
            'numero_flota' => 33,
            'estado' => 'activo',
        ]);

        $this->vehiculo->rutas()->attach($this->ruta->id, ['activo' => true]);

        $roleConductor = Role::firstOrCreate(['name' => 'conductor', 'guard_name' => 'web']);

        $this->userConductor = User::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $this->conductor->id,
            'name' => 'Mario Vargas Chofer',
            'email' => 'W1A333',
            'password' => bcrypt('password123'),
            'activo' => true,
        ]);
        $this->userConductor->assignRole($roleConductor);
    }

    public function test_salida_desde_ancash_y_lima_bloquea_ica_y_yauris_por_ser_los_mas_cercanos(): void
    {
        $validos = RutaParadero::paraderosLlegadaValidos($this->ruta->id, $this->pAncashLima->id);
        $validosNombres = $validos->pluck('nombre')->toArray();

        // No debe permitir volver a los terminales más cercanos (Ica ni Yauris)
        $this->assertNotContains('Ica', $validosNombres);
        $this->assertNotContains('Yauris', $validosNombres);

        // Sí debe permitir llegar al extremo opuesto (Jr Santa Rosa) y al otro intermedio
        $this->assertContains('Jr Santa Rosa', $validosNombres);
        $this->assertContains('S. Beatriz - S. Lucia', $validosNombres);
    }

    public function test_salida_desde_beatriz_lucia_bloquea_santa_rosa_por_ser_el_mas_cercano(): void
    {
        $validos = RutaParadero::paraderosLlegadaValidos($this->ruta->id, $this->pBeatrizLucia->id);
        $validosNombres = $validos->pluck('nombre')->toArray();

        // No debe permitir volver al origen más cercano (Jr Santa Rosa)
        $this->assertNotContains('Jr Santa Rosa', $validosNombres);

        // Sí debe permitir llegar al extremo opuesto (Ica, Yauris) y al otro intermedio
        $this->assertContains('Ica', $validosNombres);
        $this->assertContains('Yauris', $validosNombres);
        $this->assertContains('Ancash y Lima', $validosNombres);
    }

    public function test_conductor_al_terminar_vuelta_no_puede_elegir_terminal_prohibido(): void
    {
        // Vuelta iniciada en Ancash y Lima
        $vuelta = Vuelta::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'ruta_id' => $this->ruta->id,
            'paradero_salida_id' => $this->pAncashLima->id,
            'fecha' => today(),
            'numero_vuelta' => 1,
            'hora_salida' => now()->format('H:i:s'),
            'latitud' => -12.072,
            'longitud' => -75.217,
            'estado' => 'activa',
            'created_by' => $this->userConductor->id,
        ]);

        // Intento 1: Terminar en Ica (Terminal prohibido) -> Debe arrojar error 422
        $responseProhibido = $this->actingAs($this->userConductor)->postJson(route('conductor.vuelta.terminar'), [
            'latitud' => -12.075,
            'longitud' => -75.220,
            'paradero_llegada_id' => $this->pIca->id,
        ]);

        $responseProhibido->assertStatus(422);
        $responseProhibido->assertJsonFragment([
            'ok' => false,
        ]);

        // Intento 2: Terminar en Jr Santa Rosa (Extremo opuesto permitido) -> Debe tener éxito
        $responsePermitido = $this->actingAs($this->userConductor)->postJson(route('conductor.vuelta.terminar'), [
            'latitud' => -12.065,
            'longitud' => -75.210,
            'paradero_llegada_id' => $this->pSantaRosa->id,
        ]);

        $responsePermitido->assertStatus(200);
        $responsePermitido->assertJsonFragment([
            'ok' => true,
        ]);

        $vuelta->refresh();
        $this->assertEquals('completada', $vuelta->estado);
        $this->assertEquals($this->pSantaRosa->id, $vuelta->paradero_llegada_id);
    }
}
