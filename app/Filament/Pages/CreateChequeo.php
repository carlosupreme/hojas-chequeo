<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Chequeos\Schemas\ChequeosForm;
use App\Models\HojaChequeo;
use BackedEnum;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CreateChequeo extends Page
{
    protected string $view = 'filament.pages.create-chequeo';

    protected static ?string $title = 'Chequeo diario';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    public ?array $data = [];

    public ?int $hojaId = null;

    public ?HojaChequeo $checkSheet = null;

    public $dateSelected;

    public $tempDateSelected;

    protected $queryString = [
        'hojaId' => ['except' => null, 'as' => 'hoja'],
    ];

    public static function getNavigationGroup(): ?string
    {
        return 'Mantenimiento';
    }

    public function mount(): void
    {
        $user = Auth::user();
        $this->form->fill([
            'nombre_operador' => $user->name,
        ]);
        $this->dateSelected = Carbon::now();
        $this->tempDateSelected = $this->dateSelected->format('Y-m-d');

        if ($this->hojaId) {
            $this->loadCheckSheet($this->hojaId);
        }
    }

    protected function loadCheckSheet(int $checkSheetId): void
    {
        $cacheKey = "hoja:detail:{$checkSheetId}";
        $cacheTtl = now()->addHours(2);

        $this->checkSheet = Cache::tags(['hojas', "hoja:{$checkSheetId}"])->remember(
            $cacheKey,
            $cacheTtl,
            fn () => HojaChequeo::with([
                'filas',
                'columnas',
                'equipo:id,nombre,tag,area,foto',
            ])
                ->select(['id', 'equipo_id', 'observaciones'])
                ->find($checkSheetId)
        );
    }

    public function resetState(): void
    {
        $this->hojaId = null;
        $this->checkSheet = null;
        $this->form->fill();
    }

    public function updateSelectedDate(): void
    {
        $this->dateSelected = Carbon::parse($this->tempDateSelected);
    }

    public function create(): void
    {
        $user = Auth::user();

        $data = [
            ...$this->form->getState(),
            'user_id' => $user->id,
            'turno_id' => $user->turno_id,
        ];

        debug($data);

        Notification::make()
            ->success()
            ->icon('heroicon-o-document-text')
            ->iconColor('success')
            ->title('Chequeo diario guardado')
            ->send();
    }

    public function form(Schema $schema): Schema
    {
        return ChequeosForm::configure($schema)->statePath('data');
    }
}
