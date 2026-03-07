<?php

namespace App\Filament\Pages;

use App\Models\CentroCosto;
use App\Models\Equipo;
use App\Models\RegistroCarga;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class RegistroCargas extends Page
{
    protected string $view = 'filament.pages.registro-cargas';

    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationLabel(): string
    {
        return 'Registro Tombolas';
    }

    public ?array $data = [];

    public ?int $equipoId = null;

    public ?array $historialFilter = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->form->fill([
            'centro_costo_id' => $user->turno?->centro_costo_id,
        ]);

        $this->historialFilterForm->fill([
            'desde' => now()->startOfDay()->toDateString(),
            'hasta' => now()->toDateString(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Select::make('centro_costo_id')
                    ->label('Centro de costo')
                    ->options(CentroCosto::pluck('nombre', 'id'))
                    ->native(false)
                    ->preload()
                    ->live()
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public function historialFilterForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('historialFilter')
            ->extraAttributes(['class' => 'w-full'])
            ->components([
                Grid::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->components([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->locale('es')
                            ->closeOnDateSelection()
                            ->live()
                            ->maxDate(now()),
                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->locale('es')
                            ->closeOnDateSelection()
                            ->live()
                            ->maxDate(now()),
                    ]),
            ]);
    }

    public function selectEquipo(int $id): void
    {
        $this->equipoId = ($this->equipoId === $id) ? null : $id;
    }

    public function registrarCarga(): void
    {
        if (! $this->equipoId) {
            return;
        }

        $state = $this->form->getState();

        RegistroCarga::create([
            'equipo_id' => $this->equipoId,
            'user_id' => Auth::id(),
            'turno_id' => Auth::user()->turno_id,
            'centro_costo_id' => $state['centro_costo_id'] ?? null,
            'registrado_en' => now(),
        ]);

        $tag = Equipo::find($this->equipoId)?->tag ?? '';

        Notification::make()
            ->success()
            ->title('Carga registrada')
            ->body("{$tag} — ".now()->format('H:i'))
            ->send();
    }

    protected function getViewData(): array
    {
        $turnoId = $this->data['turno_id'] ?? null;

        // Tombolas with today's total pre-loaded — avoids N+1 in the selector grid.
        $tombolas = Equipo::where('tag', 'like', '%-TOM-%')
            ->withCount(['registroCargas' => fn ($q) => $q->whereDate('registrado_en', today())])
            ->orderBy('tag')
            ->get();

        $equipo = $this->equipoId ? Equipo::find($this->equipoId) : null;

        $cargasHoy = collect();
        $stats = ['hoy' => 0, 'turno' => 0, 'yo' => 0];

        if ($this->equipoId) {
            $cargasHoy = RegistroCarga::forEquipo($this->equipoId)
                ->today()
                ->with('user', 'turno')
                ->latest('registrado_en')
                ->get();

            $base = fn () => RegistroCarga::forEquipo($this->equipoId)->today();

            $stats = [
                'hoy' => $base()->count(),
                'turno' => $turnoId ? $base()->forTurno($turnoId)->count() : 0,
                'yo' => $base()->forUser(Auth::id())->count(),
            ];
        }

        $historial = collect();
        $canSeeHistorial = Auth::user()->hasRole(['Administrador', 'Supervisor']);

        if ($canSeeHistorial) {
            $desde = Carbon::parse($this->historialFilter['desde'] ?? today())->startOfDay();
            $hasta = Carbon::parse($this->historialFilter['hasta'] ?? today())->endOfDay();

            $historial = RegistroCarga::with(['equipo', 'user', 'turno', 'centroCosto'])
                ->whereHas('equipo', fn ($q) => $q->where('tag', 'like', '%-TOM-%'))
                ->whereBetween('registrado_en', [$desde, $hasta])
                ->latest('registrado_en')
                ->get();
        }

        return compact('tombolas', 'equipo', 'cargasHoy', 'stats', 'historial', 'canSeeHistorial');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Produccion';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
