<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Chequeos\Schemas\ChequeosForm;
use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\WithImageService;
use BackedEnum;
use Carbon\Carbon;
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
            ->encendidas()
            ->availableTo($this->user->perfil)
            ->findOrFail($this->hojaId);
        $this->loadFormData();
    }

    public function loadFormData(): void
    {
        $this->form->fill([
            'nombre_operador' => $this->hojaEjecucion?->nombre_operador ?? $this->user->name,
            'firma_operador' => $this->hojaEjecucion?->firma_operador ? $this->imageService()->getAsBase64($this->hojaEjecucion->firma_operador) : null,
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
            'hoja_chequeo_id' => $this->hojaChequeo->id,
        ];

        if ($this->hojaEjecucion) {
            if ($data['firma_operador'] === $this->imageService()->getAsBase64($this->hojaEjecucion->firma_operador)) {
                $data['firma_operador'] = $this->hojaEjecucion->firma_operador;
            } else {
                $data['firma_operador'] = $this->imageService()->storeBase64('firmas', $data['firma_operador']);
            }

            $this->hojaEjecucion->update($data);
            $this->dispatch('hoja-ejecucion-saved', $this->ejecucionId);

            return;
        }

        $data['firma_operador'] = $this->imageService()->storeBase64('firmas', $data['firma_operador']);
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
