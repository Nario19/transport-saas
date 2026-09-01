<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Vehiculo;
use App\Models\Conductor;
use App\Models\Tributo;
use Spatie\Permission\Models\Role;

class ReporteTributosTest extends TestCase
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
            'name' => 'Admin Reportes',
            'email' => 'reportes@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa->id,
            'activo' => true,
        ]);
        $this->admin->assignRole($role);
    }

    public function test_reporte_de_tributos_muestra_totales_cobrados_en_efectivo_y_digital(): void
    {
        $conductor = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Luis',
            'apellidos' => 'Condor',
            'dni' => '11223344',
            'primer_ingreso' => false,
            'estado' => 'activo',
        ]);

        $vehiculo1 = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $conductor->id,
            'placa' => 'W1A-123',
            'numero_flota' => 12,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'estado' => 'activo',
        ]);

        $vehiculo2 = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $conductor->id,
            'placa' => 'W2B-456',
            'numero_flota' => 14,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'estado' => 'activo',
        ]);

        Tributo::ensureGenerados($this->empresa->id);

        // Cobrar vehiculo 1 en efectivo
        $tributo1 = Tributo::where('vehiculo_id', $vehiculo1->id)->first();
        $tributo1->update([
            'metodo_pago' => 'efectivo',
            'estado' => 'pagado',
            'cobrado_por' => $this->admin->id,
            'cobrado_at' => now(),
        ]);

        // Cobrar vehiculo 2 con Yape
        $tributo2 = Tributo::where('vehiculo_id', $vehiculo2->id)->first();
        $tributo2->update([
            'metodo_pago' => 'yape',
            'estado' => 'pagado',
            'cobrado_por' => $this->admin->id,
            'cobrado_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('reportes.tributos', [
            'desde' => today()->toDateString(),
            'hasta' => today()->toDateString(),
            'flota' => '',
        ]));

        $response->assertStatus(200);
        $response->assertSee('W1A-123');
        $response->assertSee('W2B-456');
        $response->assertSee('EFECTIVO');
        $response->assertSee('#7c3aed'); // Verifica el fondo morado para pago digital
    }
}
