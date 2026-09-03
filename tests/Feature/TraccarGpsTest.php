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

class TraccarGpsTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Vehiculo $vehiculo;
    private Conductor $conductor;
    private Ruta $ruta;
    private Vuelta $vuelta;

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

        $this->conductor = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Carlos',
            'apellidos' => 'Mendoza',
            'dni' => '44556677',
            'primer_ingreso' => false,
            'estado' => 'activo',
        ]);

        $this->vehiculo = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $this->conductor->id,
            'placa' => 'W1A-777',
            'numero_flota' => 15,
            'estado' => 'activo',
        ]);

        $this->ruta = Ruta::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Ruta Principal',
            'origen' => 'Paradero A',
            'destino' => 'Paradero B',
            'estado' => 'activa',
        ]);

        $this->vuelta = Vuelta::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'ruta_id' => $this->ruta->id,
            'fecha' => today(),
            'numero_vuelta' => 1,
            'hora_salida' => '08:00:00',
            'latitud' => -12.060000,
            'longitud' => -75.200000,
            'lat_actual' => -12.060000,
            'lng_actual' => -75.200000,
            'estado' => 'activa',
            'created_by' => 1,
        ]);
    }

    public function test_traccar_actualiza_ubicacion_por_placa_via_get(): void
    {
        // Traccar Client envía petición GET con placa limpia
        $response = $this->get('/api/gps/traccar?id=W1A777&lat=-12.065432&lon=-75.210987&speed=35&bearing=180');

        $response->assertStatus(200);
        $this->assertEquals('OK', $response->getContent());

        $this->vuelta->refresh();
        $this->assertEquals(-12.065432, (float) $this->vuelta->lat_actual);
        $this->assertEquals(-75.210987, (float) $this->vuelta->lng_actual);
    }

    public function test_traccar_actualiza_ubicacion_por_numero_de_flota_via_post(): void
    {
        // Traccar Client envía petición POST con número de flota
        $response = $this->post('/api/gps/traccar', [
            'id' => '15',
            'lat' => -12.071111,
            'lon' => -75.215555,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('OK', $response->getContent());

        $this->vuelta->refresh();
        $this->assertEquals(-12.071111, (float) $this->vuelta->lat_actual);
        $this->assertEquals(-75.215555, (float) $this->vuelta->lng_actual);
    }

    public function test_traccar_actualiza_ubicacion_por_dni_conductor(): void
    {
        // Traccar Client envía petición GET con DNI del chofer
        $response = $this->get('/api/gps/traccar?id=44556677&lat=-12.080000&lon=-75.220000');

        $response->assertStatus(200);
        $this->assertEquals('OK', $response->getContent());

        $this->vuelta->refresh();
        $this->assertEquals(-12.080000, (float) $this->vuelta->lat_actual);
        $this->assertEquals(-75.220000, (float) $this->vuelta->lng_actual);
    }

    public function test_traccar_valida_coordenadas_invalidas(): void
    {
        $response = $this->get('/api/gps/traccar?id=W1A777&lat=invalido&lon=-75.210');
        $response->assertStatus(400);

        $responseMissingId = $this->get('/api/gps/traccar?lat=-12.065&lon=-75.210');
        $responseMissingId->assertStatus(400);
    }

    public function test_traccar_responde_ok_si_no_hay_vuelta_activa(): void
    {
        // Marcar vuelta como completada
        $this->vuelta->update(['estado' => 'completada']);

        $response = $this->get('/api/gps/traccar?id=W1A777&lat=-12.065&lon=-75.210');
        $response->assertStatus(200);
    }
}
