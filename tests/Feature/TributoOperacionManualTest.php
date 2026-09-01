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

class TributoOperacionManualTest extends TestCase
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
            'name' => 'Cajero / Despachador',
            'email' => 'cajero@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa->id,
            'activo' => true,
        ]);
        $this->admin->assignRole($role);

        $this->conductor = Conductor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Manuel',
            'apellidos' => 'Ramos',
            'dni' => '10203040',
            'primer_ingreso' => false,
            'estado' => 'activo',
        ]);

        $this->vehiculo = Vehiculo::create([
            'empresa_id' => $this->empresa->id,
            'conductor_id' => $this->conductor->id,
            'placa' => 'W1A-777',
            'numero_flota' => 7,
            'marca' => 'Toyota',
            'modelo' => 'Hiace',
            'estado' => 'activo',
        ]);
    }

    public function test_sistema_genera_tributos_diarios_automaticamente_para_unidades_activas(): void
    {
        Tributo::ensureGenerados($this->empresa->id);

        $this->assertDatabaseHas('tributos', [
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'monto' => 24.00,
            'estado' => 'pendiente',
        ]);
    }

    public function test_cajero_puede_cobrar_tributo_en_efectivo_letra_normal(): void
    {
        $tributo = Tributo::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'fecha' => today()->toDateString(),
            'monto' => 24.00,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($this->admin)->post(route('tributos.cobrar', $tributo->id), [
            'metodo_pago' => 'efectivo',
        ]);

        $response->assertSessionHasNoErrors();
        $tributo->refresh();

        $this->assertEquals('pagado', $tributo->estado);
        $this->assertEquals('efectivo', $tributo->metodo_pago);
        $this->assertEquals($this->admin->id, $tributo->cobrado_por);
        $this->assertNotNull($tributo->cobrado_at);
    }

    public function test_cajero_puede_cobrar_tributo_en_efectivo_morado_yape_digital(): void
    {
        $tributo = Tributo::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'fecha' => today()->toDateString(),
            'monto' => 24.00,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($this->admin)->post(route('tributos.cobrar', $tributo->id), [
            'metodo_pago' => 'yape',
        ]);

        $response->assertSessionHasNoErrors();
        $tributo->refresh();

        $this->assertEquals('pagado', $tributo->estado);
        $this->assertEquals('yape', $tributo->metodo_pago);
    }

    public function test_admin_puede_exonerar_tributo_del_dia_con_motivo(): void
    {
        $tributo = Tributo::create([
            'empresa_id' => $this->empresa->id,
            'vehiculo_id' => $this->vehiculo->id,
            'conductor_id' => $this->conductor->id,
            'fecha' => today()->toDateString(),
            'monto' => 24.00,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($this->admin)->post(route('tributos.exonerar', $tributo->id), [
            'motivo_exoneracion' => 'Mantenimiento mecánico en taller',
        ]);

        $response->assertSessionHasNoErrors();
        $tributo->refresh();

        $this->assertEquals('exonerado', $tributo->estado);
        $this->assertEquals('Mantenimiento mecánico en taller', $tributo->motivo_exoneracion);
    }
}
