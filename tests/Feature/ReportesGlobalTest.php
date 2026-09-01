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
use App\Models\Sancion;
use Spatie\Permission\Models\Role;

class ReportesGlobalTest extends TestCase
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
            'name' => 'Admin Reportes Maestro',
            'email' => 'reportes_maestro@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa->id,
            'activo' => true,
        ]);
        $this->admin->assignRole($role);

        $this->conductor = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Javier',
            'apellidos' => 'Toledo',
            'dni' => '66778899',
            'licencia_vence' => today()->addDays(15),
            'estado' => 'activo',
        ]);

        $this->vehiculo = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $this->conductor->id,
            'placa' => 'W1A-777',
            'numero_flota' => 77,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'soat_vence' => today()->addDays(10),
            'estado' => 'activo',
        ]);
    }

    public function test_dashboard_general_de_reportes_carga_resumen_mensual(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reportes.index'));

        $response->assertStatus(200);
        $response->assertSee('Centro de Reportes');
    }

    public function test_reporte_de_vueltas_muestra_recorridos_en_el_rango(): void
    {
        $ruta = Ruta::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Huancayo - Concepcion',
            'origen' => 'Huancayo',
            'destino' => 'Concepcion',
            'estado' => 'activa',
        ]);

        Vuelta::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'ruta_id' => $ruta->id,
            'fecha' => today()->toDateString(),
            'numero_vuelta' => 1,
            'hora_salida' => '08:00:00',
            'hora_llegada' => '09:00:00',
            'estado' => 'completada',
        ]);

        $response = $this->actingAs($this->admin)->get(route('reportes.vueltas', [
            'desde' => today()->toDateString(),
            'hasta' => today()->toDateString(),
            'flota' => 77,
        ]));

        $response->assertStatus(200);
        $response->assertSee('W1A-777');
        $response->assertSee('Huancayo - Concepcion');
    }

    public function test_reporte_de_sanciones_muestra_infracciones_del_periodo(): void
    {
        Sancion::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'fecha' => today()->toDateString(),
            'monto' => 45.00,
            'motivo' => 'No respetar paradero autorizado',
            'estado' => 'pagado',
            'cobrado_at' => now(),
            'metodo_pago' => 'efectivo',
        ]);

        $response = $this->actingAs($this->admin)->get(route('reportes.sanciones', [
            'desde' => today()->toDateString(),
            'hasta' => today()->toDateString(),
            'flota' => 77,
        ]));

        $response->assertStatus(200);
        $response->assertSee('W1A-777');
        $response->assertSee('No respetar paradero autorizado');
    }

    public function test_reporte_de_documentos_detecta_soat_y_licencias_proximas_a_vencer(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reportes.documentos', [
            'flota' => 77,
        ]));

        $response->assertStatus(200);
        $response->assertSee('W1A-777');
        $response->assertSee('Javier Toledo');
    }
}
