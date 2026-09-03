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
        $response->assertSee('Centro de Alertas');
        $response->assertSee('Control Policial El Tambo');
    }

    public function test_admin_puede_emitir_nueva_alerta_de_operativo_en_tiempo_real(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.alertas.store'), [
            'titulo' => 'Operativo Policial',
            'punto' => 'Control Policial El Tambo',
            'mensaje' => 'Revisión técnica vehicular',
            'tipo' => 'operativo',
            'visible_conductor' => '1',
            'duracion_minutos' => 60,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('alertas_operativos', [
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Operativo Policial',
            'punto' => 'Control Policial El Tambo',
            'mensaje' => 'Revisión técnica vehicular',
            'tipo' => 'operativo',
            'visible_conductor' => 1,
            'estado' => 'activo',
        ]);
    }

    public function test_admin_puede_alternar_visibilidad_para_conductor(): void
    {
        $alerta = AlertaOperativo::create([
            'empresa_id' => $this->empresa->id,
            'user_id' => $this->admin->id,
            'titulo' => 'Desvío Avenida Real',
            'punto' => 'Control Policial El Tambo',
            'visible_conductor' => true,
            'estado' => 'activo',
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.alertas.toggle-visibilidad', $alerta->id));

        $response->assertSessionHasNoErrors();
        $alerta->refresh();

        $this->assertFalse($alerta->visible_conductor);
    }

    public function test_admin_puede_finalizar_alerta_activa(): void
    {
        $alerta = AlertaOperativo::create([
            'empresa_id' => $this->empresa->id,
            'user_id' => $this->admin->id,
            'titulo' => 'Inspección',
            'punto' => 'Control Policial El Tambo',
            'estado' => 'activo',
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.alertas.finalizar', $alerta->id));

        $response->assertSessionHasNoErrors();
        $alerta->refresh();

        $this->assertEquals('finalizado', $alerta->estado);
    }

    public function test_admin_puede_agregar_y_eliminar_tipo_de_alerta(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tipos-alerta.store'), [
            'nombre' => 'CONTINENTAL',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tipos_alerta', [
            'empresa_id' => $this->empresa->id,
            'nombre' => 'CONTINENTAL',
        ]);

        $tipo = \App\Models\TipoAlerta::where('nombre', 'CONTINENTAL')->first();

        $delResponse = $this->actingAs($this->admin)->delete(route('admin.tipos-alerta.destroy', $tipo->id));
        $delResponse->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('tipos_alerta', [
            'id' => $tipo->id,
        ]);
    }

    public function test_admin_puede_reemitir_alerta_desde_el_historial(): void
    {
        $alertaPrevia = AlertaOperativo::create([
            'empresa_id' => $this->empresa->id,
            'user_id' => $this->admin->id,
            'titulo' => 'Control Policial Prevención',
            'punto' => 'Control Policial El Tambo',
            'mensaje' => 'Revisión exhaustiva',
            'tipo' => 'Operativo / Control',
            'visible_conductor' => true,
            'estado' => 'finalizado',
            'expires_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.alertas.reemitir', $alertaPrevia->id), [
            'duracion_minutos' => 120,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('alertas_operativos', [
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Control Policial Prevención',
            'punto' => 'Control Policial El Tambo',
            'mensaje' => 'Revisión exhaustiva',
            'estado' => 'activo',
        ]);
    }
}
