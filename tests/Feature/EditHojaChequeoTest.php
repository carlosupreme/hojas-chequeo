<?php

namespace Tests\Feature;

use App\Filament\Resources\HojaChequeos\Pages\EditHojaChequeo;
use App\Livewire\CreateHojaChequeoItems;
use App\Models\AnswerType;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaColumna;
use App\Models\HojaFila;
use App\Models\HojaFilaValor;
use App\Models\Perfil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditHojaChequeoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private HojaChequeo $hoja;

    private HojaColumna $columna;

    private HojaFila $fila;

    private HojaFilaValor $valor;

    protected function setUp(): void
    {
        parent::setUp();

        $perfil = Perfil::factory()->accesoTotal()->create();
        $this->user = User::factory()->create(['perfil_id' => $perfil->id, 'turno_id' => null]);
        $this->user->assignRole(\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']));

        $this->hoja = HojaChequeo::factory()
            ->for(Equipo::factory())
            ->create(['encendido' => true, 'version' => 1, 'observaciones' => null]);

        $answerType = AnswerType::factory()->create();

        $this->columna = HojaColumna::factory()->create([
            'hoja_chequeo_id' => $this->hoja->id,
            'key' => 'item',
            'label' => 'Item',
            'is_fixed' => true,
            'order' => 0,
        ]);

        $this->fila = HojaFila::factory()->create([
            'hoja_chequeo_id' => $this->hoja->id,
            'answer_type_id' => $answerType->id,
            'categoria' => 'limpieza',
            'order' => 0,
        ]);

        $this->valor = HojaFilaValor::create([
            'hoja_fila_id' => $this->fila->id,
            'hoja_columna_id' => $this->columna->id,
            'valor' => 'Valor original',
        ]);
    }

    // =========================================================================
    // CreateHojaChequeoItems — edit mode state on mount
    // =========================================================================

    public function test_mounts_in_edit_mode_with_has_new_items_false(): void
    {
        Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id])
            ->assertSet('isEditMode', true)
            ->assertSet('hasNewItems', false);
    }

    public function test_loads_existing_columnas_with_db_id(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);

        $columnas = $component->get('columnas');
        $first = reset($columnas);

        $this->assertEquals($this->columna->id, $first['db_id']);
        $this->assertEquals('Item', $first['label']);
    }

    public function test_loads_existing_filas_with_db_id(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);

        $filas = $component->get('filas');
        $first = reset($filas);

        $this->assertEquals($this->fila->id, $first['db_id']);
        $this->assertEquals('limpieza', $first['categoria']);
    }

    public function test_loads_existing_cell_values(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);

        $filaId = array_key_first($component->get('filas'));
        $columnId = array_key_first($component->get('columnas'));

        $this->assertEquals('Valor original', $component->get("valores.{$filaId}.{$columnId}"));
    }

    // =========================================================================
    // CreateHojaChequeoItems — hasNewItems flag
    // =========================================================================

    public function test_add_fila_sets_has_new_items_true(): void
    {
        Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id])
            ->call('addFila')
            ->assertSet('hasNewItems', true);
    }

    public function test_add_columna_sets_has_new_items_true(): void
    {
        Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id])
            ->call('addColumna')
            ->assertSet('hasNewItems', true);
    }

    public function test_remove_fila_does_not_set_has_new_items(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);
        $filaId = array_key_first($component->get('filas'));

        $component->call('removeFila', $filaId)
            ->assertSet('hasNewItems', false);
    }

    public function test_remove_columna_does_not_set_has_new_items(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);
        $columnId = array_key_first($component->get('columnas'));

        $component->call('removeColumna', $columnId)
            ->assertSet('hasNewItems', false);
    }

    public function test_editing_cell_value_does_not_set_has_new_items(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);
        $filaId = array_key_first($component->get('filas'));
        $columnId = array_key_first($component->get('columnas'));

        $component->set("valores.{$filaId}.{$columnId}", 'Nuevo valor')
            ->assertSet('hasNewItems', false);
    }

    // =========================================================================
    // CreateHojaChequeoItems — check-has-new-items event replies
    // =========================================================================

    public function test_check_has_new_items_replies_false_with_no_additions(): void
    {
        Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id])
            ->dispatch('check-has-new-items')
            ->assertDispatched('has-new-items-result', hasNew: false);
    }

    public function test_check_has_new_items_replies_true_after_add_fila(): void
    {
        Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id])
            ->call('addFila')
            ->dispatch('check-has-new-items')
            ->assertDispatched('has-new-items-result', hasNew: true);
    }

    public function test_check_has_new_items_replies_true_after_add_columna(): void
    {
        Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id])
            ->call('addColumna')
            ->dispatch('check-has-new-items')
            ->assertDispatched('has-new-items-result', hasNew: true);
    }

    public function test_check_has_new_items_replies_false_after_only_removing(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);
        $filaId = array_key_first($component->get('filas'));

        $component->call('removeFila', $filaId)
            ->dispatch('check-has-new-items')
            ->assertDispatched('has-new-items-result', hasNew: false);
    }

    // =========================================================================
    // CreateHojaChequeoItems — update-items-in-place: DB mutations
    // =========================================================================

    public function test_update_in_place_updates_columna_label_in_db(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);
        $columnId = array_key_first($component->get('columnas'));

        $component
            ->set("columnas.{$columnId}.label", 'Nueva Etiqueta')
            ->set("columnas.{$columnId}.key", 'nueva-etiqueta')
            ->dispatch('update-items-in-place', hojaChequeoId: $this->hoja->id);

        $this->assertDatabaseHas('hoja_columnas', [
            'id' => $this->columna->id,
            'label' => 'Nueva Etiqueta',
            'key' => 'nueva-etiqueta',
        ]);
    }

    public function test_update_in_place_updates_fila_categoria_in_db(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);
        $filaId = array_key_first($component->get('filas'));

        $component
            ->set("filas.{$filaId}.categoria", 'revision')
            ->dispatch('update-items-in-place', hojaChequeoId: $this->hoja->id);

        $this->assertDatabaseHas('hoja_filas', [
            'id' => $this->fila->id,
            'categoria' => 'revision',
        ]);
    }

    public function test_update_in_place_updates_cell_valor_in_db(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);
        $filaId = array_key_first($component->get('filas'));
        $columnId = array_key_first($component->get('columnas'));

        $component
            ->set("valores.{$filaId}.{$columnId}", 'Valor actualizado')
            ->dispatch('update-items-in-place', hojaChequeoId: $this->hoja->id);

        $this->assertDatabaseHas('hoja_fila_valors', [
            'hoja_fila_id' => $this->fila->id,
            'hoja_columna_id' => $this->columna->id,
            'valor' => 'Valor actualizado',
        ]);
    }

    public function test_update_in_place_deletes_empty_valor_from_db(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);
        $filaId = array_key_first($component->get('filas'));
        $columnId = array_key_first($component->get('columnas'));

        $component
            ->set("valores.{$filaId}.{$columnId}", '')
            ->dispatch('update-items-in-place', hojaChequeoId: $this->hoja->id);

        $this->assertDatabaseMissing('hoja_fila_valors', [
            'hoja_fila_id' => $this->fila->id,
            'hoja_columna_id' => $this->columna->id,
        ]);
    }

    public function test_update_in_place_deletes_removed_fila_from_db(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);
        $filaId = array_key_first($component->get('filas'));

        $component
            ->call('removeFila', $filaId)
            ->dispatch('update-items-in-place', hojaChequeoId: $this->hoja->id);

        $this->assertDatabaseMissing('hoja_filas', ['id' => $this->fila->id]);
    }

    public function test_update_in_place_deletes_removed_columna_from_db(): void
    {
        $component = Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id]);
        $columnId = array_key_first($component->get('columnas'));

        $component
            ->call('removeColumna', $columnId)
            ->dispatch('update-items-in-place', hojaChequeoId: $this->hoja->id);

        $this->assertDatabaseMissing('hoja_columnas', ['id' => $this->columna->id]);
    }

    public function test_update_in_place_does_not_create_new_hoja_chequeo(): void
    {
        $count = HojaChequeo::count();

        Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id])
            ->dispatch('update-items-in-place', hojaChequeoId: $this->hoja->id);

        $this->assertEquals($count, HojaChequeo::count());
    }

    public function test_update_in_place_dispatches_simple_updated_event(): void
    {
        Livewire::test(CreateHojaChequeoItems::class, ['hojaChequeoId' => $this->hoja->id])
            ->dispatch('update-items-in-place', hojaChequeoId: $this->hoja->id)
            ->assertDispatched('hoja-chequeo-simple-updated');
    }

    // =========================================================================
    // EditHojaChequeo page — branching logic
    // =========================================================================

    public function test_update_dispatches_check_has_new_items(): void
    {
        $this->actingAs($this->user);

        Livewire::test(EditHojaChequeo::class, ['record' => $this->hoja->id])
            ->call('update')
            ->assertDispatched('check-has-new-items');
    }

    public function test_update_stores_pending_data(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(EditHojaChequeo::class, ['record' => $this->hoja->id]);
        $component->set('data.observaciones', '<p>Pendiente</p>');
        $component->call('update');

        $this->assertNotNull($component->get('pendingData'));
    }

    public function test_no_new_items_updates_existing_record_fields(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(EditHojaChequeo::class, ['record' => $this->hoja->id]);
        $component->set('data.observaciones', '<p>Nueva observacion</p>');
        $component->call('update');
        $component->dispatch('has-new-items-result', hasNew: false);

        $this->assertDatabaseHas('hoja_chequeos', [
            'id' => $this->hoja->id,
            'observaciones' => '<p>Nueva observacion</p>',
        ]);
    }

    public function test_no_new_items_does_not_create_new_hoja_chequeo(): void
    {
        $this->actingAs($this->user);

        $count = HojaChequeo::count();

        $component = Livewire::test(EditHojaChequeo::class, ['record' => $this->hoja->id]);
        $component->call('update');
        $component->dispatch('has-new-items-result', hasNew: false);

        $this->assertEquals($count, HojaChequeo::count());
    }

    public function test_no_new_items_dispatches_update_items_in_place(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(EditHojaChequeo::class, ['record' => $this->hoja->id]);
        $component->call('update');
        $component->dispatch('has-new-items-result', hasNew: false)
            ->assertDispatched('update-items-in-place', hojaChequeoId: $this->hoja->id);
    }

    public function test_no_new_items_keeps_old_record_encendido(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(EditHojaChequeo::class, ['record' => $this->hoja->id]);
        $component->call('update');
        $component->dispatch('has-new-items-result', hasNew: false);

        $this->assertTrue($this->hoja->fresh()->encendido);
    }

    public function test_has_new_items_creates_new_version(): void
    {
        $this->actingAs($this->user);

        $count = HojaChequeo::count();

        $component = Livewire::test(EditHojaChequeo::class, ['record' => $this->hoja->id]);
        $component->call('update');
        $component->dispatch('has-new-items-result', hasNew: true);

        $this->assertEquals($count + 1, HojaChequeo::count());
    }

    public function test_has_new_items_turns_off_old_record(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(EditHojaChequeo::class, ['record' => $this->hoja->id]);
        $component->call('update');
        $component->dispatch('has-new-items-result', hasNew: true);

        $this->assertFalse($this->hoja->fresh()->encendido);
    }

    public function test_has_new_items_dispatches_hoja_chequeo_created_with_new_id(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(EditHojaChequeo::class, ['record' => $this->hoja->id]);
        $component->call('update');
        $component->dispatch('has-new-items-result', hasNew: true)
            ->assertDispatched('hoja-chequeo-created');

        // New record ID should differ from original
        $newHoja = HojaChequeo::where('id', '!=', $this->hoja->id)->latest()->first();
        $this->assertNotNull($newHoja);
    }

    public function test_simple_updated_event_sends_notification(): void
    {
        $this->actingAs($this->user);

        Livewire::test(EditHojaChequeo::class, ['record' => $this->hoja->id])
            ->dispatch('hoja-chequeo-simple-updated')
            ->assertNotified('Cambios guardados');
    }

    public function test_version_created_event_sends_notification(): void
    {
        $this->actingAs($this->user);

        Livewire::test(EditHojaChequeo::class, ['record' => $this->hoja->id])
            ->dispatch('create-hoja-chequeo-items-created')
            ->assertNotified('Nueva version creada');
    }
}
