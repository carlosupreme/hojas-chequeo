<?php

namespace Tests\Feature;

use App\Livewire\ChequeoItems;
use App\Models\AnswerType;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaColumna;
use App\Models\HojaEjecucion;
use App\Models\HojaFila;
use App\Models\Perfil;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChequeoItemsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private HojaChequeo $hoja;

    protected function setUp(): void
    {
        parent::setUp();

        $perfil = Perfil::factory()->accesoTotal()->create();
        $this->user = User::factory()->create(['perfil_id' => $perfil->id, 'turno_id' => null]);
        $this->user->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Operador', 'guard_name' => 'web']));

        $this->hoja = HojaChequeo::factory()
            ->for(Equipo::factory())
            ->create(['encendido' => true]);

        // At least one columna is required for the blade view ($columnas[0])
        HojaColumna::factory()->create(['hoja_chequeo_id' => $this->hoja->id]);

        // HojaEjecucionObserver queries this role when finalizado_en is set
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function filaOfType(string $typeKey): HojaFila
    {
        $type = AnswerType::factory()->{$typeKey}()->create();

        return HojaFila::factory()->create([
            'hoja_chequeo_id' => $this->hoja->id,
            'answer_type_id' => $type->id,
        ]);
    }

    private function mountFresh(?HojaEjecucion $ejecucion = null): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test(ChequeoItems::class, [
            'hoja' => $this->hoja,
            'ejecucion' => $ejecucion,
        ]);
    }

    // -------------------------------------------------------------------------
    // 1. Any form change dispatches chequeo-item-changed to parent
    // -------------------------------------------------------------------------

    public function test_updated_form_dispatches_chequeo_item_changed(): void
    {
        $fila = $this->filaOfType('number');

        $this->mountFresh()
            ->set("form.{$fila->id}", 10)
            ->assertDispatched('chequeo-item-changed', filaId: $fila->id, value: 10);
    }

    // -------------------------------------------------------------------------
    // 2–4. onEjecucionEnsured persists HojaFilaRespuesta for each answer type
    // -------------------------------------------------------------------------

    public function test_on_ejecucion_ensured_saves_numeric_respuesta(): void
    {
        $fila = $this->filaOfType('number');
        $ejecucion = HojaEjecucion::factory()->create(['hoja_chequeo_id' => $this->hoja->id, 'user_id' => $this->user->id]);

        $this->mountFresh()
            ->dispatch('chequeo-ejecucion-ensured', ejecucionId: $ejecucion->id, filaId: $fila->id, value: 42.5);

        $this->assertDatabaseHas('hoja_fila_respuestas', [
            'hoja_ejecucion_id' => $ejecucion->id,
            'hoja_fila_id' => $fila->id,
            'numeric_value' => 42.5,
        ]);
    }

    public function test_on_ejecucion_ensured_saves_text_respuesta(): void
    {
        $fila = $this->filaOfType('text');
        $ejecucion = HojaEjecucion::factory()->create(['hoja_chequeo_id' => $this->hoja->id, 'user_id' => $this->user->id]);

        $this->mountFresh()
            ->dispatch('chequeo-ejecucion-ensured', ejecucionId: $ejecucion->id, filaId: $fila->id, value: 'Normal');

        $this->assertDatabaseHas('hoja_fila_respuestas', [
            'hoja_ejecucion_id' => $ejecucion->id,
            'hoja_fila_id' => $fila->id,
            'text_value' => 'Normal',
        ]);
    }

    public function test_on_ejecucion_ensured_saves_boolean_respuesta(): void
    {
        $fila = $this->filaOfType('boolean');
        $ejecucion = HojaEjecucion::factory()->create(['hoja_chequeo_id' => $this->hoja->id, 'user_id' => $this->user->id]);

        $this->mountFresh()
            ->dispatch('chequeo-ejecucion-ensured', ejecucionId: $ejecucion->id, filaId: $fila->id, value: true);

        $this->assertDatabaseHas('hoja_fila_respuestas', [
            'hoja_ejecucion_id' => $ejecucion->id,
            'hoja_fila_id' => $fila->id,
            'boolean_value' => true,
        ]);
    }

    // -------------------------------------------------------------------------
    // 5. onEjecucionEnsured updates an existing respuesta (idempotent)
    // -------------------------------------------------------------------------

    public function test_on_ejecucion_ensured_updates_existing_respuesta(): void
    {
        $fila = $this->filaOfType('number');
        $ejecucion = HojaEjecucion::factory()->create(['hoja_chequeo_id' => $this->hoja->id, 'user_id' => $this->user->id]);

        $component = $this->mountFresh();

        $component->dispatch('chequeo-ejecucion-ensured', ejecucionId: $ejecucion->id, filaId: $fila->id, value: 10);
        $component->dispatch('chequeo-ejecucion-ensured', ejecucionId: $ejecucion->id, filaId: $fila->id, value: 99);

        // Only one record, with the updated value
        $this->assertDatabaseCount('hoja_fila_respuestas', 1);
        $this->assertDatabaseHas('hoja_fila_respuestas', ['numeric_value' => 99]);
    }

    // -------------------------------------------------------------------------
    // 6. save() stores ALL respuestas when hoja-ejecucion-saved fires
    // -------------------------------------------------------------------------

    public function test_save_stores_all_respuestas_on_hoja_ejecucion_saved(): void
    {
        $fila1 = $this->filaOfType('number');
        $fila2 = $this->filaOfType('text');
        $ejecucion = HojaEjecucion::factory()->create(['hoja_chequeo_id' => $this->hoja->id, 'user_id' => $this->user->id]);

        $this->mountFresh()
            ->set("form.{$fila1->id}", 55)
            ->set("form.{$fila2->id}", 'Ok')
            ->dispatch('hoja-ejecucion-saved', $ejecucion->id);

        $this->assertDatabaseHas('hoja_fila_respuestas', ['hoja_fila_id' => $fila1->id, 'numeric_value' => 55]);
        $this->assertDatabaseHas('hoja_fila_respuestas', ['hoja_fila_id' => $fila2->id, 'text_value' => 'Ok']);
    }

    // -------------------------------------------------------------------------
    // 7. save() sets finalizado_en when ALL items are answered
    // -------------------------------------------------------------------------

    public function test_save_sets_finalizado_en_when_all_items_answered(): void
    {
        $fila = $this->filaOfType('number');
        $ejecucion = HojaEjecucion::factory()->create(['hoja_chequeo_id' => $this->hoja->id, 'user_id' => $this->user->id]);

        $this->mountFresh()
            ->set("form.{$fila->id}", 10) // answered
            ->dispatch('hoja-ejecucion-saved', $ejecucion->id);

        $this->assertNotNull($ejecucion->fresh()->finalizado_en);
    }

    // -------------------------------------------------------------------------
    // 8. save() does NOT set finalizado_en when any item is null
    // -------------------------------------------------------------------------

    public function test_save_does_not_set_finalizado_en_when_items_incomplete(): void
    {
        $this->filaOfType('number'); // fila created but left NULL in form
        $ejecucion = HojaEjecucion::factory()->create(['hoja_chequeo_id' => $this->hoja->id, 'user_id' => $this->user->id]);

        $this->mountFresh()
            // form[fila->id] is null (never set)
            ->dispatch('hoja-ejecucion-saved', $ejecucion->id);

        $this->assertNull($ejecucion->fresh()->finalizado_en);
    }

    public function test_save_uses_forced_finalizado_date_when_provided(): void
    {
        $fila = $this->filaOfType('number');
        $ejecucion = HojaEjecucion::factory()->create(['hoja_chequeo_id' => $this->hoja->id, 'user_id' => $this->user->id]);
        $forcedDate = Carbon::now()->subDays(2)->toDateString();

        $this->mountFresh()
            ->set("form.{$fila->id}", 10)
            ->dispatch('hoja-ejecucion-saved', hojaEjecucionId: $ejecucion->id, forcedFinalizadoEn: $forcedDate);

        $this->assertEquals($forcedDate, $ejecucion->fresh()->finalizado_en->toDateString());
    }

    // -------------------------------------------------------------------------
    // 9. Mounting with an existing ejecucion pre-fills form with saved answers
    // -------------------------------------------------------------------------

    public function test_mounts_with_existing_responses_pre_filled(): void
    {
        $fila = $this->filaOfType('number');
        $ejecucion = HojaEjecucion::factory()->create(['hoja_chequeo_id' => $this->hoja->id, 'user_id' => $this->user->id]);

        // Seed an existing response
        \App\Models\HojaFilaRespuesta::create([
            'hoja_ejecucion_id' => $ejecucion->id,
            'hoja_fila_id' => $fila->id,
            'numeric_value' => 77,
        ]);

        $component = $this->mountFresh($ejecucion);

        $this->assertEquals(77, $component->get("form.{$fila->id}"));
    }
}
