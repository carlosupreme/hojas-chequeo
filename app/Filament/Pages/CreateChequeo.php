<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Chequeos\Schemas\ChequeosForm;
use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\User;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class CreateChequeo extends Page
{
    protected string $view = 'filament.pages.create-chequeo';

    public ?HojaEjecucion $ejecucion = null;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    public ?array $data = [];

    public null|string|int $hojaId = null;

    public ?HojaChequeo $hojaChequeo = null;

    public $dateSelected;

    public $user;

    protected $queryString = [
        'hojaId' => ['except' => '', 'as' => 'h'],
    ];

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->form->fill([
            'nombre_operador' => $this->user->name,
        ]);
        $this->dateSelected = Carbon::now();

        if ($this->hojaId) {
            $this->loadHojaChequeo($this->hojaId);
        }
    }

    #[On('hojaChequeoSelected')]
    public function nextPage(int $hojaId): void
    {
        $this->hojaId = $hojaId;
        $this->loadHojaChequeo($hojaId);
    }

    protected function loadHojaChequeo(int $hojaId): void
    {
        $this->hojaChequeo = HojaChequeo::with(['filas.valores.hojaColumna', 'columnas', 'equipo'])
            ->encendidas()
            ->availableTo($this->user->perfil)
            ->findOrFail($hojaId);
    }

    public function resetState(): void
    {
        $this->hojaId = null;
        $this->hojaChequeo = null;
        $this->form->fill([
            'nombre_operador' => $this->user->name,
        ]);
        $this->dateSelected = Carbon::now();
    }

    public function hasItems(): bool
    {
        return $this->hojaChequeo?->hasItems();
    }

    public function create(): void
    {
        $data = [
            ...$this->form->getState(),
            'user_id' => $this->user->id,
            'turno_id' => $this->user->turno_id,
            'created_at' => $this->dateSelected,
        ];

        debug($data);

        Notification::make()
            ->success()
            ->icon('heroicon-o-document-text')
            ->iconColor('success')
            ->title('Chequeo diario guardado')
            ->send();
    }

    public function dateForm(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('dateSelected')
                ->disabled(fn ($component) => !$this->user->can(User::$canEditDatesPermission))
                ->hiddenLabel()
                ->displayFormat('D d/m/Y')
                ->native(false)
                ->locale('es')
                ->closeOnDateSelection()
                ->required()
                ->maxDate(now()),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return ChequeosForm::configure($schema)->statePath('data');
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
