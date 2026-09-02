<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\PuntoControl;
use App\Models\AlertaOperativo;
use Spatie\Permission\Models\Role;

class AlertasOperativosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Empresa $empresa;
    private PuntoControl $punto;

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

        $roleAdmin = Role::firstOrCreate([
            'name' => 'e' . $this->empresa->id . '_ADMIN',
            'guard_name' => 'web'
        ]);

        $this->admin = User::create([
            'name' => 'Admin Alertas',
            'email' => 'admin_alertas@transjunin.com',
            'password' => bcrypt('password123'),
            'empresa_id' => $this->empresa->id,
            'activo' => true,
        ]);
        $this->admin->assignRole($roleAdmin);

        $this->punto = PuntoControl::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Control Policial El Tambo',
        ]);
    }

    public function test_admin_puede_ver_modulo_de_alertas_operativos_en_el_panel(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.alertas.index'));

        $response->assertStatus(200);
        $response->assertSee('Alertas de Operativos');
        $response->assertSee('Control Policial El Tambo');
    }

    public function test_admin_puede_emitir_nueva_alerta_de_operativo_en_tiempo_real(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.alertas.store'), [
            'punto' => 'Control Policial El Tambo',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('alertas_operativos', [
            'empresa_id' => $this->empresa->id,
            'punto' => 'Control Policial El Tambo',
            'estado' => 'activo',
        ]);
    }

    public function test_admin_puede_finalizar_alerta_activa(): void
    {
        $alerta = AlertaOperativo::create([
            'empresa_id' => $this->empresa->id,
            'user_id' => $this->admin->id,
            'punto' => 'Control Policial El Tambo',
            'estado' => 'activo',
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.alertas.finalizar', $alerta->id));

        $response->assertSessionHasNoErrors();
        $alerta->refresh();

        $this->assertEquals('finalizado', $alerta->estado);
    }
}
