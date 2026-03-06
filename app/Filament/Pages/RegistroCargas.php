<?php

namespace App\Filament\Pages;

use App\Models\CentroCosto;
use App\Models\Equipo;
use App\Models\RegistroCarga;
use App\Models\Turno;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class RegistroCargas extends Page
{
    protected string $view = 'filament.pages.registro-cargas';

    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationLabel(): string
    {
        return 'Registro Cargas';
    }

    public ?array $data = [];

    public ?int $equipoId = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->form->fill([
            'turno_id' => $user->turno_id,
            'centro_costo_id' => $user->turno?->centro_costo_id,
        ]);
    }

    /**
     * Context selectors — pre-filled from the user's assigned turno/centro_costo
     * but freely changeable during operation (e.g. operator covers two shifts).
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Select::make('turno_id')
                    ->label('Turno')
                    ->options(Turno::where('activo', true)->pluck('nombre', 'id'))
                    ->native(false)
                    ->preload()
                    ->live()
                    ->required(),
                Select::make('centro_costo_id')
                    ->label('Centro de costo')
                    ->options(CentroCosto::pluck('nombre', 'id'))
                    ->native(false)
                    ->preload()
                    ->live()
                    ->required(),
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
            'turno_id' => $state['turno_id'] ?? null,
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

        return compact('tombolas', 'equipo', 'cargasHoy', 'stats');
    }
}
