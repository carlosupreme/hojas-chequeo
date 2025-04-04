<?php

namespace App\Livewire;

use App\Models\HojaChequeo;
use App\Models\Reporte;
use App\Models\User;
use Carbon\Carbon;
use Coolsam\SignaturePad\Forms\Components\Fields\SignaturePad;
use Filament\Notifications\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ChequeoDiario extends Component implements HasForms
{
    use InteractsWithForms;

    public bool $reported = false;

    public int $page = 1;
    public ?HojaChequeo $checkSheet;

    public ?array $data = [];


    public $dateSelected;
    public $tempDateSelected;

    public function mount(): void
    {
        $this->form->fill();
        $this->dateSelected = Carbon::now();
        $this->tempDateSelected = $this->dateSelected->format('Y-m-d');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()->schema([
                    SignaturePad::make('firma_operador')
                        ->label('Firma')
                        ->hideDownloadButtons()
                        ->required(),
                    TextInput::make('nombre_operador')
                        ->default(fn() => auth()->user()->name)
                        ->label('Nombre')
                        ->required(),
                ]),
                Textarea::make('observaciones')
                    ->translateLabel()
                    ->columnSpanFull(),
            ])
            ->statePath('data')
            ->model(\App\Models\ChequeoDiario::class);
    }


    #[On('checkSheetSelected')]
    public function nextPage(int $checkSheet): void
    {
        $this->page = 2;
        $this->checkSheet = HojaChequeo::with('items')->find($checkSheet);
    }

    public function hasItems(): bool
    {
        if ($this->checkSheet) {
            return $this->checkSheet->items->count() > 0;
        }
        return false;
    }

    public function save(): void
    {
        $this->dispatch('requestForValidItems');
    }

    #[On('validItems')]
    public function creating(): void
    {
        $data = $this->form->getState();
        $created = \App\Models\ChequeoDiario::create([
            ...$data,
            'hoja_chequeo_id' => $this->checkSheet->id,
            'operador_id' => auth()->id(),
            'created_at' => $this->dateSelected
        ]);
        $this->dispatch('dailyCheckCreated', $created->id);
    }

    public function updateSelectedDate()
    {

        $this->dateSelected = Carbon::parse($this->tempDateSelected);

    }

    #[On('invalidItems')]
    public function invalidItems(): void
    {
        $this->addError('items', 'Algunos items no han sido completados');
    }

    public function saveAndReport(): void
    {
        $this->save();
        $this->reported = true;
    }

    #[On('dailyCheckItemsSaved')]
    public function allSaved(): void
    {
        Notification::make()
            ->success()
            ->icon('heroicon-o-document-text')
            ->iconColor('success')
            ->title('Chequeo diario guardado')
            ->send();

        Notification::make()
            ->title('Chequeo diario guardado')
            ->success()
            ->icon('heroicon-o-document-text')
            ->iconColor('success')
            ->body(auth()->user()->name . ' ha completado un chequeo diario para la hoja ' . $this->checkSheet->name)
            ->actions([
                Actions\Action::make('Ver')
                    ->button()
                    ->url("/admin/hoja-chequeos/{$this->checkSheet->id}/historial?startDate=" . now()->format('Y-m-d') . '&endDate=' . now()->format('Y-m-d'))
            ])
            ->sendToDatabase(User::role('Administrador')->get(), isEventDispatched: true);

        if ($this->reported) {
            Reporte::create([
                'equipo_id' => $this->checkSheet->equipo->id,
                'hoja_chequeo_id' => $this->checkSheet->id,
                'fecha' => Carbon::now()
            ]);
            $this->redirect('https://mantenimientotintoreriatacuba.netlify.app/');
        } else {
            $this->resetState();
        }
    }

    #[On('dailyCheckItemsFailed')]
    public function onError($dailyCheckId): void
    {
        \App\Models\ChequeoDiario::destroy($dailyCheckId);
    }

    public function resetState(): void
    {
        $this->checkSheet = null;
        $this->form->fill();
        $this->page = 1;
    }

    public function render()
    {
        return view('livewire.chequeo-diario', ['user' => Auth::user()]);
    }
}
