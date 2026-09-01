<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Propietario;
use App\Models\Vehiculo;
use Spatie\Permission\Models\Role;

class ReporteDeudasTest extends TestCase
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

    public function test_reporte_de_deudas_monto_de_ingreso_muestra_columnas_correctas(): void
    {
        $socio = Propietario::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Luis',
            'apellidos' => 'Condor',
            'dni' => '99887766',
            'tipo_persona' => 'socio',
            'activo' => true,
        ]);

        Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'propietario_id' => $socio->id,
            'placa' => 'X1X-999',
            'numero_flota' => 10,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($this->admin)->get(route('reportes.deudas', [
            'tipo' => 'monto_ingreso',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Condición');
        $response->assertSee('Monto Inicial');
        $response->assertSee('Cuota 1');
        $response->assertSee('Cuota 2');
        $response->assertSee('Cuota 3');
        $response->assertSee('SOCIO DE LA EMPRESA');
        $response->assertSee('EXONERADO');
    }
}
