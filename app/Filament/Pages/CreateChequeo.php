<?php

namespace App\Filament\Pages;

use App\Area;
use App\Events\ChequeoAutoSaved;
use App\Filament\Resources\Chequeos\Schemas\ChequeosForm;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\Reporte;
use App\Models\User;
use App\WithImageService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class CreateChequeo extends Page
{
    use WithImageService;

    protected string $view = 'filament.pages.create-chequeo';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    // Hide default Filament page header — we render our own sticky header
    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return null;
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public static function getNavigationLabel(): string
    {
        return 'Chequeo diario';
    }

    public ?array $data = [];

    #[Url(as: 'h', except: null)]
    public null|string|int $hojaId = null;

    public null|string|int $centroCostoId = null;

    public ?HojaChequeo $hojaChequeo = null;

    public ?HojaEjecucion $hojaEjecucion = null;

    public $dateSelected;

    public $user;

    public bool $autoSaving = false;

    public bool $dateWasChanged = false;

    public bool $esPpm = false;

    protected ?string $originalDateSelected = null;

    #[Url(as: 'e', except: null)]
    public null|string|int $ejecucionId = null;

    #[Url(as: 'b', except: null)]
    public ?string $backUrl = null;

    public function mount(): void
    {
        $this->user = Auth::user();

        if ($this->ejecucionId) {
            $this->loadEjecucion();
        }

        $this->loadFormData();

        if ($this->hojaId && ! $this->ejecucionId) {
            $this->loadHojaChequeo();
        }
    }

    #[On('hojaChequeoSelected')]
    public function setHojaChequeo(array $data): void
    {
        $this->hojaId = $data['id'];
        $this->centroCostoId = $data['centro_costo'];
        $this->loadHojaChequeo();
    }

    #[On('hojaEjecucionSelected')]
    public function setHojaEjecucion(array $data): void
    {
        $this->ejecucionId = $data['id'];
        $this->centroCostoId = $data['centro_costo'];
        $this->loadEjecucion();
    }

    protected function loadEjecucion(): void
    {
        $this->hojaEjecucion = HojaEjecucion::findOrFail($this->ejecucionId);
        $this->hojaId = $this->hojaEjecucion->hoja_chequeo_id;
        $this->loadHojaChequeo();
    }

    protected function loadHojaChequeo(): void
    {
        $this->dispatch('scroll-to-top');
        $this->hojaChequeo = HojaChequeo::with(['filas.valores.hojaColumna', 'columnas', 'equipo'])
            ->encendidas(! $this->hojaEjecucion)
            ->availableTo($this->user->perfil)
            ->findOrFail($this->hojaId);
        $this->loadFormData();
    }

    public function loadFormData(): void
    {
        $this->form->fill([
            'nombre_operador' => $this->hojaEjecucion?->nombre_operador ?? $this->user->name,
            'firma_operador' => $this->hojaEjecucion?->firma_operador ? $this->imageService()
                ->getAsBase64($this->hojaEjecucion->firma_operador) : null,
            'observaciones' => $this->hojaEjecucion?->observaciones ?? '',
        ]);

        $this->dateSelected = $this->hojaEjecucion?->created_at ?? Carbon::now();
        $this->originalDateSelected = $this->normalizeDateForComparison($this->dateSelected);
        $this->dateWasChanged = false;
    }

    public function updatedDateSelected(mixed $value): void
    {
        $currentDate = $this->normalizeDateForComparison($value);

        if (! $this->canOverrideExecutionDates() || is_null($currentDate)) {
            $this->dateWasChanged = false;

            return;
        }

        $this->dateWasChanged = $currentDate !== $this->originalDateSelected;
    }

    public function resetState(): void
    {
        $this->hojaId = null;
        $this->centroCostoId = null;
        $this->hojaChequeo = null;
        $this->ejecucionId = null;
        $this->hojaEjecucion = null;
        $this->form->fill([
            'nombre_operador' => $this->user->name,
        ]);
        $this->dateSelected = Carbon::now();
        if (! is_null($this->backUrl)) {
            redirect()->to($this->backUrl);
        }
    }

    public function hasItems(): bool
    {
        return $this->hojaChequeo?->hasItems();
    }

    public function updated(string $name): void
    {
        // Save Filament form fields (nombre_operador, observaciones, etc.)
        if (str_starts_with($name, 'data.') && $this->hojaChequeo) {
            $this->autoSave();
        }
    }

    /**
     * Triggered when a checklist item inside ChequeoItems changes.
     * Ensures the HojaEjecucion exists, then dispatches back so
     * ChequeoItems can persist the individual HojaFilaRespuesta.
     */
    #[On('chequeo-item-changed')]
    public function handleItemChanged(int $filaId, mixed $value): void
    {
        if (! $this->hojaChequeo) {
            return;
        }

        $this->autoSave();

        if ($this->hojaEjecucion) {
            $this->dispatch('chequeo-ejecucion-ensured',
                ejecucionId: $this->hojaEjecucion->id,
                filaId: $filaId,
                value: $value,
            );
        }
    }

    protected function autoSave(): void
    {
        // $this->data is the raw form state — no validation triggered
        $state = $this->data;

        // Ensure nombre_operador is always populated (pre-filled via loadFormData)
        $state['nombre_operador'] ??= $this->user->name;
        if (empty($state['nombre_operador'])) {
            return;
        }

        $this->dispatch('chequeo-autosave-saving');

        $data = [
            ...$state,
            'user_id' => $this->user->id,
            'turno_id' => $this->user->turno_id,
            'centro_costo_id' => $this->centroCostoId,
            'created_at' => $this->dateSelected,
            'hoja_chequeo_id' => $this->hojaChequeo->id,
            'es_ppm' => $this->esPpm,
        ];

        if ($this->shouldOverrideExecutionDates() && $this->hojaEjecucion?->finalizado_en) {
            $data['finalizado_en'] = $this->dateSelected;
        }

        // Firma is handled only on final submit to avoid repeated file writes
        unset($data['firma_operador']);

        if ($this->hojaEjecucion) {
            $this->hojaEjecucion->update($data);
        } else {
            $this->hojaEjecucion = HojaEjecucion::create($data);
            $this->ejecucionId = $this->hojaEjecucion->id;
        }

        broadcast(new ChequeoAutoSaved($this->hojaEjecucion))->toOthers();

        $this->dispatch('chequeo-autosave-saved');
    }

    public function activatePpm(): void
    {
        $this->esPpm = true;
        $this->dispatch('ppm-activated');
        $this->data['observaciones'] = ($this->data['observaciones'] ?? '')."\nPPM";

        if ($this->ejecucionId) {
            HojaEjecucion::where('id', $this->ejecucionId)->update(['es_ppm' => true]);
        }
    }

    public function deactivatePpm(): void
    {
        $this->esPpm = false;
        $this->dispatch('ppm-deactivated');
        $this->data['observaciones'] = str_replace("\nPPM", '', $this->data['observaciones'] ?? '');
        if ($this->ejecucionId) {
            HojaEjecucion::where('id', $this->ejecucionId)->update(['es_ppm' => false]);
        }
    }

    public function reportAction(): Action
    {
        return Action::make('report')
            ->label('Reportar falla')
            ->icon(Heroicon::OutlinedExclamationTriangle)
            ->color('danger')
            ->modalHeading('Nuevo Reporte de Falla')
            ->modalDescription('Complete los campos para registrar un reporte de falla del equipo.')
            ->modalSubmitActionLabel('Crear Reporte')
            ->fillForm(fn () => [
                'nombre' => $this->data['nombre_operador'] ?? $this->user->name,
                'equipo_id' => $this->hojaChequeo?->equipo_id,
                'observaciones' => $this->data['observaciones'] ?? '',
                'fecha' => now(),
                'prioridad' => 'media',
            ])
            ->schema([
                TextInput::make('nombre')
                    ->label('Nombre del operador')
                    ->required(),
                Hidden::make('fecha')->default($this->dateSelected),
                Select::make('equipo_id')
                    ->label('Equipo')
                    ->options(fn () => Equipo::pluck('tag', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('area')
                    ->label('Área')
                    ->options(collect(Area::cases())
                        ->mapWithKeys(fn (Area $area) => [
                            $area->value => $area->label(),
                        ])
                        ->toArray()
                    )
                    ->required(),
                Select::make('prioridad')
                    ->label('Prioridad')
                    ->options([
                        'alta' => 'Alta',
                        'media' => 'Media',
                        'baja' => 'Baja',
                    ])
                    ->required(),
                TextInput::make('falla')
                    ->label('Descripción de la falla')
                    ->required(),
                Textarea::make('observaciones')
                    ->label('Observaciones'),
                FileUpload::make('foto')
                    ->label('Evidencia fotográfica')
                    ->image()
                    ->directory('reportes'),
            ])
            ->action(function (array $data): void {
                Reporte::create([
                    ...$data,
                    'user_id' => $this->user->id,
                    'hoja_chequeo_id' => $this->hojaChequeo?->id,
                    'estado' => 'pendiente',
                ]);

                Notification::make()
                    ->success()
                    ->title('Reporte creado')
                    ->body('El reporte de falla ha sido registrado correctamente.')
                    ->send();
            });
    }

    public function create(): void
    {
        $forcedFinalizadoEn = $this->getForcedFinalizadoEnForSave();

        $data = [
            ...$this->form->getState(),
            'firma_operador' => $this->data['firma_operador'] ?? null,
            'user_id' => $this->user->id,
            'turno_id' => $this->user->turno_id,
            'centro_costo_id' => $this->centroCostoId,
            'created_at' => $this->dateSelected,
            'hoja_chequeo_id' => $this->hojaChequeo->id,
            'es_ppm' => $this->esPpm,
        ];

        if ($forcedFinalizadoEn && $this->hojaEjecucion?->finalizado_en) {
            $data['finalizado_en'] = $forcedFinalizadoEn;
        }

        if ($data['firma_operador']) {
            $data['firma_operador'] = $this->imageService()->storeBase64('firmas', $data['firma_operador']);
        }

        if ($this->hojaEjecucion) {
            $this->hojaEjecucion->update($data);
            $this->dispatch('hoja-ejecucion-saved', hojaEjecucionId: $this->ejecucionId, forcedFinalizadoEn: $forcedFinalizadoEn);

            return;
        }

        $hojaEjecucion = HojaEjecucion::create($data);
        $this->dispatch('hoja-ejecucion-saved', hojaEjecucionId: $hojaEjecucion->id, forcedFinalizadoEn: $forcedFinalizadoEn);
    }

    protected function normalizeDateForComparison(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value)->toDateString();
    }

    protected function canOverrideExecutionDates(): bool
    {
        return $this->user?->can(User::$canEditDatesPermission) ?? false;
    }

    protected function shouldOverrideExecutionDates(): bool
    {
        return $this->canOverrideExecutionDates() && $this->dateWasChanged;
    }

    protected function getForcedFinalizadoEnForSave(): ?string
    {
        if (! $this->shouldOverrideExecutionDates() || blank($this->dateSelected)) {
            return null;
        }

        return Carbon::parse($this->dateSelected)->toDateString();
    }

    #[On('hoja-fila-respuesta-items-created')]
    public function showSuccessNotification(): void
    {
        Notification::make()
            ->success()
            ->icon('heroicon-o-document-text')
            ->iconColor('success')
            ->title('Chequeo diario guardado')
            ->send();

        $this->resetState();
    }

    public function dateForm(Schema $schema): Schema
    {
        return ChequeosForm::date($schema);
    }

    public function form(Schema $schema): Schema
    {
        return ChequeosForm::base($schema)->statePath('data');
    }

    public function signatureForm(Schema $schema): Schema
    {
        return ChequeosForm::signature($schema)->statePath('data');
    }

    protected function getForms(): array
    {
        return ['form', 'signatureForm', 'dateForm'];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Mantenimiento';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function getExtraBodyAttributes(): array
    {
        return [
            'class' => 'create-chequeo-page',
        ];
    }
}
