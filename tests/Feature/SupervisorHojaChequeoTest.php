<?php

namespace Tests\Feature;

use App\Filament\Resources\HojaChequeos\Pages\ListHojaChequeos;
use App\Models\AnswerType;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaColumna;
use App\Models\HojaFila;
use App\Models\Perfil;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupervisorHojaChequeoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $supervisor;

    private HojaChequeo $hoja;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);

        $perfil = Perfil::factory()->accesoTotal()->create();

        $this->admin = User::factory()->create(['perfil_id' => $perfil->id, 'turno_id' => null]);
        $this->admin->assignRole('Administrador');

        $this->supervisor = User::factory()->create(['perfil_id' => $perfil->id, 'turno_id' => null]);
        $this->supervisor->assignRole('Supervisor');

        $equipo = Equipo::factory()->create();

        $this->hoja = HojaChequeo::factory()->create([
            'equipo_id' => $equipo->id,
            'version' => 1,
            'encendido' => true,
        ]);

        $columna = HojaColumna::factory()->create([
            'hoja_chequeo_id' => $this->hoja->id,
            'key' => 'item',
            'label' => 'Item',
            'is_fixed' => true,
            'order' => 0,
        ]);

        $answerType = AnswerType::factory()->create();

        $fila = HojaFila::factory()->create([
            'hoja_chequeo_id' => $this->hoja->id,
            'answer_type_id' => $answerType->id,
            'categoria' => 'limpieza',
            'order' => 0,
        ]);
    }

    public function test_supervisor_can_access_hojas_chequeo_list(): void
    {
        $response = $this->actingAs($this->supervisor)->get('/supervisor/hoja-chequeos');

        $response->assertOk();
    }

    public function test_supervisor_can_access_history_page(): void
    {
        $response = $this->actingAs($this->supervisor)->get("/supervisor/hoja-chequeos/{$this->hoja->id}/historial");

        $response->assertOk();
    }

    public function test_supervisor_cannot_access_create_page(): void
    {
        $response = $this->actingAs($this->supervisor)->get('/supervisor/hoja-chequeos/crear');

        $response->assertForbidden();
    }

    public function test_supervisor_cannot_access_edit_page(): void
    {
        $response = $this->actingAs($this->supervisor)->get("/supervisor/hoja-chequeos/{$this->hoja->id}/editar");

        $response->assertForbidden();
    }

    public function test_supervisor_cannot_access_versions_page(): void
    {
        $response = $this->actingAs($this->supervisor)->get("/supervisor/hoja-chequeos/{$this->hoja->id}/versiones");

        $response->assertForbidden();
    }

    public function test_supervisor_table_actions_visibility(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('supervisor'));

        Livewire::actingAs($this->supervisor)
            ->test(ListHojaChequeos::class)
            ->assertOk()
            ->assertActionHidden('create')
            ->assertTableActionVisible('Historial', $this->hoja->id)
            ->assertTableActionHidden('edit', $this->hoja->id)
            ->assertTableActionHidden('delete', $this->hoja->id)
            ->assertTableActionHidden('Copiar', $this->hoja->id)
            ->assertTableActionHidden('Versiones', $this->hoja->id);
    }

    public function test_admin_has_full_access_to_hoja_chequeos(): void
    {
        $this->actingAs($this->admin)->get('/admin/hoja-chequeos')->assertOk();
        $this->actingAs($this->admin)->get('/admin/hoja-chequeos/crear')->assertOk();
        $this->actingAs($this->admin)->get("/admin/hoja-chequeos/{$this->hoja->id}/editar")->assertOk();
        $this->actingAs($this->admin)->get("/admin/hoja-chequeos/{$this->hoja->id}/historial")->assertOk();
        $this->actingAs($this->admin)->get("/admin/hoja-chequeos/{$this->hoja->id}/versiones")->assertOk();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($this->admin)
            ->test(ListHojaChequeos::class)
            ->assertOk()
            ->assertActionVisible('create')
            ->assertTableActionVisible('Historial', $this->hoja->id)
            ->assertTableActionVisible('edit', $this->hoja->id)
            ->assertTableActionVisible('delete', $this->hoja->id)
            ->assertTableActionVisible('Copiar', $this->hoja->id);
    }
}
