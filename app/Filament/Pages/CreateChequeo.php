<?php

namespace App\Filament\Pages;

use App\Area;
use App\Filament\Resources\Chequeos\Schemas\ChequeosForm;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\Reporte;
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

    public static function getNavigationLabel(): string
    {
        return 'Chequeo diario';
    }

    public ?array $data = [];

    #[Url(as: 'h', except: null)]
    public null|string|int $hojaId = null;

    public ?HojaChequeo $hojaChequeo = null;

    public ?HojaEjecucion $hojaEjecucion = null;

    public $dateSelected;

    public $user;

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
    public function setHojaChequeo(int $hojaId): void
    {
        $this->hojaId = $hojaId;
        $this->loadHojaChequeo();
    }

    #[On('hojaEjecucionSelected')]
    public function setHojaEjecucion(int $ejecucionId): void
    {
        $this->ejecucionId = $ejecucionId;
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
    }

    public function resetState(): void
    {
        $this->hojaId = null;
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
        $data = [
            ...$this->form->getState(),
            'user_id' => $this->user->id,
            'turno_id' => $this->user->turno_id,
            'created_at' => $this->dateSelected,
            'hoja_chequeo_id' => $this->hojaChequeo->id,
        ];

        if ($data['firma_operador']) {
            $data['firma_operador'] = $this->imageService()->storeBase64('firmas', $data['firma_operador']);
        }

        if ($this->hojaEjecucion) {
            $this->hojaEjecucion->update($data);
            $this->dispatch('hoja-ejecucion-saved', $this->ejecucionId);

            return;
        }

        $hojaEjecucion = HojaEjecucion::create($data);
        $this->dispatch('hoja-ejecucion-saved', $hojaEjecucion->id);
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
