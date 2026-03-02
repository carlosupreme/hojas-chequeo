<?php

namespace Tests\Feature;

use App\Filament\Pages\CreateChequeo;
use App\Models\AnswerType;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaColumna;
use App\Models\HojaEjecucion;
use App\Models\HojaFila;
use App\Models\Perfil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateChequeoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private HojaChequeo $hoja;

    private HojaFila $fila;

    protected function setUp(): void
    {
        parent::setUp();

        $perfil = Perfil::factory()->accesoTotal()->create();
        $this->user = User::factory()->create(['perfil_id' => $perfil->id, 'turno_id' => null]);

        // Give the user a role so Spatie doesn't complain
        $this->user->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Operador', 'guard_name' => 'web']));

        $answerType = AnswerType::factory()->number()->create();
        $this->hoja = HojaChequeo::factory()
            ->for(Equipo::factory())
            ->create(['encendido' => true]);

        // At least one columna is required for the blade view ($columnas[0])
        HojaColumna::factory()->create(['hoja_chequeo_id' => $this->hoja->id]);

        $this->fila = HojaFila::factory()->create([
            'hoja_chequeo_id' => $this->hoja->id,
            'answer_type_id' => $answerType->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // 1. AUTOSAVE: creates a new HojaEjecucion on the first item change
    // -------------------------------------------------------------------------

    public function test_autosave_creates_hoja_ejecucion_on_first_item_change(): void
    {
        $this->actingAs($this->user);

        $livewire = Livewire::withQueryParams(['h' => $this->hoja->id])
            ->test(CreateChequeo::class)
            ->set('centroCostoId', null);

        $this->assertDatabaseCount('hoja_ejecucions', 0);

        $livewire->call('handleItemChanged', $this->fila->id, 42.5);

        $this->assertDatabaseCount('hoja_ejecucions', 1);
        $this->assertDatabaseHas('hoja_ejecucions', [
            'hoja_chequeo_id' => $this->hoja->id,
            'user_id' => $this->user->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // 2. AUTOSAVE: updates the existing HojaEjecucion on subsequent changes
    // -------------------------------------------------------------------------

    public function test_autosave_updates_existing_hoja_ejecucion_not_creates_new(): void
    {
        $this->actingAs($this->user);

        $livewire = Livewire::withQueryParams(['h' => $this->hoja->id])
            ->test(CreateChequeo::class);

        // First change → creates
        $livewire->call('handleItemChanged', $this->fila->id, 10);
        $this->assertDatabaseCount('hoja_ejecucions', 1);

        // Second change → updates, does NOT create a second row
        $livewire->call('handleItemChanged', $this->fila->id, 20);
        $this->assertDatabaseCount('hoja_ejecucions', 1);
    }

    // -------------------------------------------------------------------------
    // 3. handleItemChanged dispatches chequeo-ejecucion-ensured back to child
    // -------------------------------------------------------------------------

    public function test_handle_item_changed_dispatches_ejecucion_ensured_event(): void
    {
        $this->actingAs($this->user);

        Livewire::withQueryParams(['h' => $this->hoja->id])
            ->test(CreateChequeo::class)
            ->call('handleItemChanged', $this->fila->id, 99)
            ->assertDispatched('chequeo-ejecucion-ensured');
    }

    // -------------------------------------------------------------------------
    // 4. Filament form field changes (data.*) also trigger autosave
    // -------------------------------------------------------------------------

    public function test_changing_nombre_operador_triggers_autosave(): void
    {
        $this->actingAs($this->user);

        Livewire::withQueryParams(['h' => $this->hoja->id])
            ->test(CreateChequeo::class)
            ->set('data.nombre_operador', 'Nuevo Operador')
            ->assertDispatched('chequeo-autosave-saved');

        $this->assertDatabaseHas('hoja_ejecucions', [
            'nombre_operador' => 'Nuevo Operador',
        ]);
    }

    // -------------------------------------------------------------------------
    // 5. firma_operador must NOT be persisted during autosave
    // -------------------------------------------------------------------------

    public function test_firma_operador_is_excluded_from_autosave(): void
    {
        $this->actingAs($this->user);

        Livewire::withQueryParams(['h' => $this->hoja->id])
            ->test(CreateChequeo::class)
            ->set('data.firma_operador', 'data:image/png;base64,FAKE')
            ->set('data.nombre_operador', 'Test User');

        // firma_operador should not be stored (it's a large base64 blob)
        $this->assertDatabaseMissing('hoja_ejecucions', [
            'firma_operador' => 'data:image/png;base64,FAKE',
        ]);
    }

    // -------------------------------------------------------------------------
    // 6. RESUME: mounting with ejecucionId loads existing data into form
    // -------------------------------------------------------------------------

    public function test_resuming_loads_ejecucion_into_form(): void
    {
        $this->actingAs($this->user);

        $ejecucion = HojaEjecucion::factory()->create([
            'hoja_chequeo_id' => $this->hoja->id,
            'user_id' => $this->user->id,
            'nombre_operador' => 'Juan Perez',
            'observaciones' => 'Nota de prueba',
        ]);

        $livewire = Livewire::withQueryParams(['e' => $ejecucion->id])
            ->test(CreateChequeo::class);

        // The form state should be pre-filled from the ejecucion
        $this->assertEquals('Juan Perez', $livewire->get('data')['nombre_operador']);
        $this->assertEquals('Nota de prueba', $livewire->get('data')['observaciones']);
    }

    // -------------------------------------------------------------------------
    // 7. FINAL SUBMIT: create() dispatches hoja-ejecucion-saved
    // -------------------------------------------------------------------------

    public function test_create_dispatches_hoja_ejecucion_saved(): void
    {
        $this->actingAs($this->user);

        Livewire::withQueryParams(['h' => $this->hoja->id])
            ->test(CreateChequeo::class)
            ->set('data.nombre_operador', 'Operador Final')
            ->set('data.firma_operador', null)
            ->set('data.observaciones', '')
            ->call('create')
            ->assertDispatched('hoja-ejecucion-saved');
    }

    // -------------------------------------------------------------------------
    // 8. FINAL SUBMIT on existing ejecucion: dispatches hoja-ejecucion-saved
    //    with the existing ID (resume + final submit)
    // -------------------------------------------------------------------------

    public function test_create_on_resumed_ejecucion_dispatches_hoja_ejecucion_saved(): void
    {
        $this->actingAs($this->user);

        $ejecucion = HojaEjecucion::factory()->create([
            'hoja_chequeo_id' => $this->hoja->id,
            'user_id' => $this->user->id,
        ]);

        $livewire = Livewire::withQueryParams(['e' => $ejecucion->id])
            ->test(CreateChequeo::class)
            ->set('data.firma_operador', null)
            ->call('create');

        $livewire->assertDispatched('hoja-ejecucion-saved', $ejecucion->id);

        // Should not have created a second ejecucion
        $this->assertDatabaseCount('hoja_ejecucions', 1);
    }
}
