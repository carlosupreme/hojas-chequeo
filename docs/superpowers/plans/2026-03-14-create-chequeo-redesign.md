# CreateChequeo Redesign Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign CreateChequeo for tablet/desktop operators with PPM support, inline icon buttons, sticky progress bar, and signature-in-modal flow.

**Architecture:** TDD throughout — tests first, then backend (migration → model → page logic → Livewire), then frontend (icon-buttons component → blade redesign). PPM stores `es_ppm=true` on HojaEjecucion and bulk-fills all items via Livewire event. Signature only appears in "Finalizar" modal.

**Tech Stack:** Laravel 12, Filament 4.0, Livewire 3, Alpine.js, Blade components

---

## Chunk 1: Backend — Migration, Model, PPM Logic

### Task 1: Migration — add es_ppm to hoja_ejecucions

**Files:**
- Create: `database/migrations/2026_03_14_000001_add_es_ppm_to_hoja_ejecucions_table.php`

- [ ] **Step 1: Write migration**

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hoja_ejecucions', function (Blueprint $table) {
            $table->boolean('es_ppm')->default(false)->after('finalizado_en');
        });
    }

    public function down(): void
    {
        Schema::table('hoja_ejecucions', function (Blueprint $table) {
            $table->dropColumn('es_ppm');
        });
    }
};
```

- [ ] **Step 2: Run migration**

Run: `php artisan migrate`
Expected: migrated successfully

- [ ] **Step 3: Confirm baseline tests still pass**

Run: `php artisan test tests/Feature/CreateChequeoTest.php tests/Feature/ChequeoItemsTest.php --no-coverage`
Expected: 19 passed

---

### Task 2: Update HojaEjecucion model + factory

**Files:**
- Modify: `app/Models/HojaEjecucion.php`
- Modify: `database/factories/HojaEjecucionFactory.php`

- [ ] **Step 1: Add es_ppm to fillable and cast**

In `HojaEjecucion.php`, add `'es_ppm'` to `$fillable` and add to `casts()`:
```php
'es_ppm' => 'boolean',
```

- [ ] **Step 2: Add ppm() factory state**

In `HojaEjecucionFactory.php`, add:
```php
public function ppm(): static
{
    return $this->state(fn (array $attributes) => [
        'es_ppm' => true,
    ]);
}
```

- [ ] **Step 3: Run tests**

Run: `php artisan test tests/Feature/CreateChequeoTest.php tests/Feature/ChequeoItemsTest.php --no-coverage`
Expected: 19 passed

---

### Task 3: PPM backend logic in CreateChequeo

**Files:**
- Modify: `app/Filament/Pages/CreateChequeo.php`

- [ ] **Step 1: Add PPM state property and methods**

Add to the class:
```php
public bool $esPpm = false;

public function activatePpm(): void
{
    $this->esPpm = true;
    $this->dispatch('ppm-activated');

    if ($this->ejecucionId) {
        HojaEjecucion::where('id', $this->ejecucionId)
            ->update(['es_ppm' => true]);
    }
}

public function deactivatePpm(): void
{
    $this->esPpm = false;
    $this->dispatch('ppm-deactivated');

    if ($this->ejecucionId) {
        HojaEjecucion::where('id', $this->ejecucionId)
            ->update(['es_ppm' => false]);
    }
}
```

- [ ] **Step 2: Persist es_ppm on autoSave and create**

In `autoSave()`, add `'es_ppm' => $this->esPpm` to the updateOrCreate call.

In `create()`, add `'es_ppm' => $this->esPpm` to the merge data.

- [ ] **Step 3: Add signatureForm() schema**

```php
public function signatureForm(Form $form): Form
{
    return $form
        ->schema([
            SignaturePad::make('firma_operador')
                ->label('Firma del operador')
                ->required(),
        ])
        ->statePath('data');
}
```

- [ ] **Step 4: Run tests**

Run: `php artisan test tests/Feature/CreateChequeoTest.php --no-coverage`
Expected: 9 passed

---

### Task 4: PPM bulk-fill in ChequeoItems

**Files:**
- Modify: `app/Livewire/ChequeoItems.php`

- [ ] **Step 1: Add progress tracking properties**

```php
public int $answeredCount = 0;
public int $totalCount = 0;
```

- [ ] **Step 2: Compute counts after mount and after each update**

Add private method:
```php
private function recomputeProgress(): void
{
    $this->totalCount = count($this->items);
    $this->answeredCount = collect($this->form)
        ->filter(fn ($row) => collect($row)->some(fn ($v) => $v !== null && $v !== '' && $v !== false))
        ->count();
}
```

Call `$this->recomputeProgress()` at the end of `mount()` and at the end of `updatedForm()`.

- [ ] **Step 3: Add bulkFillPpm listener**

```php
#[On('ppm-activated')]
public function bulkFillPpm(): void
{
    $realizadoId = \App\Models\AnswerOption::where('key', 'realizado')->value('id');

    foreach ($this->items as $fila) {
        $filaId = $fila['id'];
        foreach ($fila['inputs'] as $input) {
            $this->form[$filaId][$input['type_key']] = match ($input['type_key']) {
                'icon_set' => $realizadoId,
                'number'   => 0,
                'text'     => ' ',
                'boolean'  => true,
                default    => null,
            };
        }
    }

    $this->recomputeProgress();
    $this->dispatch('chequeo-items-bulk-filled');
}

#[On('ppm-deactivated')]
public function clearPpmFill(): void
{
    // Reset all form values to null
    foreach ($this->items as $fila) {
        $filaId = $fila['id'];
        foreach ($fila['inputs'] as $input) {
            $this->form[$filaId][$input['type_key']] = null;
        }
    }
    $this->recomputeProgress();
}
```

- [ ] **Step 4: Dispatch progress event on update**

In `updatedForm()`, after `recomputeProgress()`, dispatch:
```php
$this->dispatch('progress-updated', answered: $this->answeredCount, total: $this->totalCount);
```

Also dispatch at end of `mount()`.

- [ ] **Step 5: Run tests**

Run: `php artisan test tests/Feature/ChequeoItemsTest.php --no-coverage`
Expected: 10 passed

---

## Chunk 2: New Tests

### Task 5: Write PPM tests

**Files:**
- Modify: `tests/Feature/CreateChequeoTest.php`
- Modify: `tests/Feature/ChequeoItemsTest.php`

- [ ] **Step 1: Add PPM tests to CreateChequeoTest**

```php
/** @test */
public function activate_ppm_sets_es_ppm_flag(): void
{
    $hoja = HojaChequeo::factory()->withItems(2)->create();
    $ejecucion = HojaEjecucion::factory()->for($hoja, 'hojaChequeo')->create();

    Livewire::test(CreateChequeo::class, ['hojaId' => $hoja->id, 'ejecucionId' => $ejecucion->id])
        ->call('activatePpm')
        ->assertSet('esPpm', true);

    $this->assertTrue($ejecucion->fresh()->es_ppm);
}

/** @test */
public function deactivate_ppm_unsets_es_ppm_flag(): void
{
    $hoja = HojaChequeo::factory()->withItems(2)->create();
    $ejecucion = HojaEjecucion::factory()->ppm()->for($hoja, 'hojaChequeo')->create();

    Livewire::test(CreateChequeo::class, ['hojaId' => $hoja->id, 'ejecucionId' => $ejecucion->id])
        ->set('esPpm', true)
        ->call('deactivatePpm')
        ->assertSet('esPpm', false);

    $this->assertFalse($ejecucion->fresh()->es_ppm);
}

/** @test */
public function create_stores_es_ppm_on_hoja_ejecucion(): void
{
    $hoja = HojaChequeo::factory()->withItems(1)->create();

    Livewire::test(CreateChequeo::class, ['hojaId' => $hoja->id])
        ->call('activatePpm')
        ->call('create');

    $this->assertTrue(HojaEjecucion::latest()->first()->es_ppm);
}
```

- [ ] **Step 2: Add PPM bulk-fill test to ChequeoItemsTest**

```php
/** @test */
public function ppm_activated_bulk_fills_all_items(): void
{
    $this->seed(AnswerSeeder::class);
    $hoja = HojaChequeo::factory()->withItems(2)->create();
    $ejecucion = HojaEjecucion::factory()->for($hoja, 'hojaChequeo')->create();
    $realizadoId = AnswerOption::where('key', 'realizado')->value('id');

    $component = Livewire::test(ChequeoItems::class, [
        'hojaChequeoId' => $hoja->id,
        'hojaEjecucionId' => $ejecucion->id,
    ]);

    $component->dispatch('ppm-activated');

    foreach ($component->get('items') as $fila) {
        $filaId = $fila['id'];
        foreach ($fila['inputs'] as $input) {
            $expected = match ($input['type_key']) {
                'icon_set' => $realizadoId,
                'number'   => 0,
                'text'     => ' ',
                'boolean'  => true,
                default    => null,
            };
            $this->assertEquals($expected, $component->get("form.{$filaId}.{$input['type_key']}"));
        }
    }
}
```

- [ ] **Step 3: Run all tests**

Run: `php artisan test tests/Feature/CreateChequeoTest.php tests/Feature/ChequeoItemsTest.php --no-coverage`
Expected: 22 passed

---

## Chunk 3: Frontend

### Task 6: icon-buttons component

**Files:**
- Create: `resources/views/components/table-inputs/icon-buttons.blade.php`

- [ ] **Step 1: Write icon-buttons component**

```blade
@props([
    'wireModel' => '',
    'options'   => [],  // [{id, label, icon, color}]
    'disabled'  => false,
])

<div class="flex gap-1" wire:key="icon-btns-{{ $wireModel }}">
    @foreach ($options as $option)
        @php
            $isSelected = (string)$wireModel === (string)$option['id'];
            $colorMap = [
                'green'  => 'border-green-500 bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                'red'    => 'border-red-500 bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                'yellow' => 'border-yellow-500 bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                'gray'   => 'border-gray-400 bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            ];
            $baseClass = 'flex items-center justify-center w-10 h-10 rounded-lg border-2 transition-all cursor-pointer';
            $selectedClass = $colorMap[$option['color']] ?? $colorMap['gray'];
            $unselectedClass = 'border-gray-200 bg-white text-gray-400 hover:border-gray-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-500';
        @endphp
        <button
            type="button"
            title="{{ $option['label'] }}"
            wire:click="$set('{{ $wireModel }}', {{ $option['id'] }})"
            @disabled($disabled)
            class="{{ $baseClass }} {{ $isSelected ? $selectedClass : $unselectedClass }}"
        >
            <x-filament::icon icon="{{ $option['icon'] }}" class="w-5 h-5" />
        </button>
    @endforeach
</div>
```

- [ ] **Step 2: Update input-dispatcher to use icon-buttons for icon_set**

In `resources/views/components/table-inputs/input-dispatcher.blade.php`, replace the `icon_set` case to render `<x-table-inputs.icon-buttons>` instead of `<x-table-inputs.icon-select>`.

---

### Task 7: Redesign chequeo-items.blade.php

**Files:**
- Modify: `resources/views/livewire/chequeo-items.blade.php`

Key changes:
- Desktop table: cleaner, row highlight when fully answered (light green bg)
- Replace dropdown icon-select with inline icon-buttons
- Emit progress via `$wire.on('progress-updated', ...)` to sticky bar

---

### Task 8: Redesign create-chequeo.blade.php

**Files:**
- Modify: `resources/views/filament/pages/create-chequeo.blade.php`

Key changes (matching approved clean-design.html):
- Single-line sticky header: Hoja name + operator name field
- Items card (full width, no sidebar)
- Observations textarea visible in body
- Sticky bottom bar: gray PPM button | progress "2/4" | green "Finalizar" button
- PPM active notice: one-line gray banner above items
- "Finalizar" opens Alpine modal with only the signature pad + submit button
- Progress bar updates via `@window.progress-updated` Alpine listener

---

### Task 9: Final test run

- [ ] Run: `php artisan test tests/Feature/CreateChequeoTest.php tests/Feature/ChequeoItemsTest.php --no-coverage`
- [ ] Expected: ≥22 passed, 0 failed
- [ ] Manual tablet check: sticky bar visible, PPM button works, modal shows signature

---
